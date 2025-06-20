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
use PDF; // se usar barryvdh/laravel-dompdf

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
        // carrega mapeamentos deste edital
        $mappings = PlaceholderMapping::where('opportunity_id', $oppId)->orderBy('priority')
            ->get();

        // busca valores de registration_meta
        $metas = DB::connection('pgsql_remote')
            ->table('registration_meta')
            ->where('object_id', $regId)
            ->pluck('value','key')
            ->toArray();

        $search  = [];
        $replace = [];

        foreach ($mappings as $map) {
            $fieldKey = "field_{$map->field_id}";
            $value = $metas[$fieldKey] ?? '';

            // placeholders com e sem espaços
            $phRaw    = '{{'.$map->placeholder_key.'}}';
            $phSpaced = '{{ '.$map->placeholder_key.' }}';

            $search[]  = $phRaw;
            $replace[] = $value;

            $search[]  = $phSpaced;
            $replace[] = $value;
        }

        // adiciona placeholder {{id}} se existir no template
        $idValue = (string) $sequenceNumber;
        $search[]  = '{{id}}';
        $replace[] = $idValue;
        $search[]  = '{{ id }}';
        $replace[] = $idValue;

        $bodyHtml = str_replace($search, $replace, $tpl->body_html);

        return [
            $tpl->header_html,
            $bodyHtml,
            $tpl->footer_html,
        ];
    }
}
