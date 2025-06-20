<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Template;
use App\Models\GeneratedTerm;
use App\Models\PlaceholderMapping;
use App\Models\ExternalOpportunity;
use App\Models\OpportunitySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDF;
use NumberFormatter;

class TermsController extends Controller
{
    /** Form para escolher Edital + Template */
    public function create()
    {
        $opportunities = ExternalOpportunity::whereNull('parent_id')
            ->where('published_registrations', true)
            ->orderBy('name')
            ->get(['id','name']);

        $templates = Template::orderBy('name')->get();

        return view('admin.terms.create', compact('opportunities','templates'));
    }

    /**
     * Gera e faz download dos PDFs (ou ZIP) dos termos
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'opportunity_id' => 'required|integer',
            'template_id'    => 'required|integer',
        ]);

        $oppId       = $data['opportunity_id'];
        $template    = Template::findOrFail($data['template_id']);

        // pega o número inicial da tabela opportunity_settings
        $setting       = OpportunitySetting::where('opportunity_id', $oppId)->first();
        $currentNumber = $setting->start_number ?? 1;

        // 1) inscrições aprovadas...
        $registrationIds = DB::connection('pgsql_remote')
            ->table('registration')
            ->where('opportunity_id', $oppId)
            ->where('status', 10)
            ->pluck('id');

        // garante diretório...
        $termsPath = storage_path('app/terms');
        if (! is_dir($termsPath)) {
            mkdir($termsPath, 0755, true);
        }

        $files = [];
        foreach ($registrationIds as $regId) {
            // 2) gera as partes do termo com placeholders substituídos, incluindo ID sequencial
            [$header, $body, $footer] = $this->buildTermParts($template, $oppId, $regId, $currentNumber);

            $pdf = PDF::loadView('pdf.term', compact('header','body','footer'))
                    ->setPaper('A4','portrait');

            // monta filename...
            $regInfo   = DB::connection('pgsql_remote')
                ->table('registration')
                ->where('id', $regId)
                ->select('number', 'agent_id')
                ->first();
            $regNumber = $regInfo->number ?? $regId;
            $agentName = DB::connection('pgsql_remote')
                ->table('agent')
                ->where('id', $regInfo->agent_id)
                ->value('name');
            $agentSlug = Str::slug($agentName ?: '');
            $filename = "term_{$oppId}_{$regNumber}_{$agentSlug}.pdf";

            $pdf->save("{$termsPath}/{$filename}");

            GeneratedTerm::create([
                'template_id'     => $template->id,
                'opportunity_id'  => $oppId,
                'registration_id' => $regId,
                'filename'        => $filename,
            ]);

            $files[] = "{$termsPath}/{$filename}";
            $currentNumber++;
        }

        // download ou zip...
        if (count($files) === 1) {
            return response()->download($files[0])->deleteFileAfterSend();
        }

        $zipName = "terms_{$oppId}.zip";
        $zipPath = "{$termsPath}/{$zipName}";
        $zip = new \ZipArchive;
        $zip->open($zipPath, \ZipArchive::CREATE|\ZipArchive::OVERWRITE);
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend();
    }

    /**
     * Gera os arrays [header, body, footer] substituindo placeholders,
     * incluindo o placeholder {{id}} se existir, com numeração sequencial e ano atual.
     */
    protected function buildTermParts(Template $tpl, int $oppId, int $regId, int $sequenceNumber): array
    {
        // 1) Pega faixas da oportunidade-pai
        $opp       = ExternalOpportunity::findOrFail($oppId);
        $parentOpp = $opp->parent_id
            ? ExternalOpportunity::findOrFail($opp->parent_id)
            : $opp;
        $ranges = json_decode($parentOpp->registration_ranges ?? '[]', true) ?: [];

        // 2) Puxa o 'range' diretamente da inscrição (table registration)
        $label = DB::connection('pgsql_remote')
            ->table('registration')
            ->where('id', $regId)
            ->value('range');

        // 3) Tenta encontrar o valor numérico correspondente
        $num = null;
        foreach ($ranges as $r) {
            if (($r['label'] ?? '') === $label) {
                $num = $r['value'];
                break;
            }
        }

        // 4) Formatação de valor + extenso, corrigindo singular/plural e centavos
        $valorReplacement = '';
        if (null !== $num) {
            // formata o R$ 18.214,28
            $fmtCur   = new NumberFormatter('pt_BR', NumberFormatter::CURRENCY);
            $formatted = $fmtCur->formatCurrency($num, 'BRL');

            // quebra parte inteira e centavos
            $intPart   = (int) floor($num);
            $cents     = (int) round(($num - $intPart) * 100);

            $fmtSpell  = new NumberFormatter('pt_BR', NumberFormatter::SPELLOUT);

            // extenso da parte inteira
            $intSpell = $fmtSpell->format($intPart);
            $intSpell = ucfirst($intSpell) . ' reais';

            // extenso dos centavos, se houver
            if ($cents > 0) {
                $centSpell = $fmtSpell->format($cents);
                $centSpell = ucfirst($centSpell) . ' centavos';
                $spell = $intSpell . ' e ' . $centSpell;
            } else {
                $spell = $intSpell;
            }

            $valorReplacement = "{$formatted} ({$spell})";
        }

        // 5) Monta substituições iniciais para {{valor}}
        $search  = ['{{valor}}','{{ valor }}'];
        $replace = [$valorReplacement, $valorReplacement];

        // 6) Busca todos os outros placeholders mapeados
        //    e substitui por registration_meta ou agente, se necessário.
        $metas = DB::connection('pgsql_remote')
            ->table('registration_meta')
            ->where('object_id', $regId)
            ->pluck('value','key')
            ->toArray();

        $mappings = PlaceholderMapping::where('opportunity_id', $oppId)
            ->orderBy('priority')
            ->get();

        foreach ($mappings as $map) {
            if ($map->placeholder_key === 'valor') {
                continue; // já tratado
            }

            $raw     = '{{'.$map->placeholder_key.'}}';
            $spaced  = '{{ '.$map->placeholder_key.' }}';
            $value   = '';

            // se for campo dinâmico, do registration_meta
            $fieldKey = "field_{$map->field_id}";
            if (isset($metas[$fieldKey])) {
                $value = $metas[$fieldKey];
            }

            $search[]  = $raw;    $replace[] = $value;
            $search[]  = $spaced; $replace[] = $value;
        }

        // 7) Sequência {{id}}
        $idVal = (string) $sequenceNumber;
        $search[]  = '{{id}}';   $replace[] = $idVal;
        $search[]  = '{{ id }}'; $replace[] = $idVal;

        // 8) Aplica em cada parte separadamente
        $header = str_replace($search, $replace, $tpl->header_html);
        $body   = str_replace($search, $replace, $tpl->body_html);
        $footer = str_replace($search, $replace, $tpl->footer_html);

        return [$header, $body, $footer];
    }
}
