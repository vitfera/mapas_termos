<?php

namespace App\Jobs;

use App\Models\ExternalOpportunity;
use App\Models\ExternalRegistrationFieldConfiguration;
use App\Models\GeneratedTerm;
use App\Models\OpportunitySetting;
use App\Models\PlaceholderMapping;
use App\Models\Template;
use App\Models\TermGenerationProcess;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NumberFormatter;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class GenerateTermsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hora
    public $tries = 1;

    protected $processId;

    public function __construct(int $processId)
    {
        $this->processId = $processId;
    }

    public function handle(): void
    {
        $process = TermGenerationProcess::findOrFail($this->processId);
        
        try {
            $process->markAsProcessing();
            
            $this->generateTerms($process);
            
        } catch (\Exception $e) {
            Log::error('Erro na geração de termos', [
                'process_id' => $this->processId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $process->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    protected function generateTerms(TermGenerationProcess $process): void
    {
        $oppId = $process->opportunity_id;
        $template = Template::findOrFail($process->template_id);

        // Pega configuração de numeração
        $setting = OpportunitySetting::firstOrCreate(
            ['opportunity_id' => $oppId],
            ['start_number' => 1, 'last_sequence' => null]
        );
        
        $currentNumber = $setting->last_sequence
            ? $setting->last_sequence + 1
            : $setting->start_number;

        // Busca inscrições aprovadas
        $registrationIds = DB::connection('pgsql_remote')
            ->table('registration')
            ->where('opportunity_id', $oppId)
            ->where('status', 10)
            ->pluck('id');

        // Atualiza total no processo
        $process->update(['total_registrations' => $registrationIds->count()]);

        if ($registrationIds->isEmpty()) {
            $process->markAsCompleted();
            return;
        }

        // Garante diretório
        $termsPath = storage_path('app/terms');
        if (!is_dir($termsPath)) {
            mkdir($termsPath, 0755, true);
        }

        $files = [];
        $initialNumber = $currentNumber;

        foreach ($registrationIds as $regId) {
            try {
                // Busca informações da inscrição
                $regInfo = DB::connection('pgsql_remote')
                    ->table('registration')
                    ->where('id', $regId)
                    ->select('number', 'agent_id')
                    ->first();
                
                $regNumber = $regInfo->number ?? $regId;

                // Gera partes do termo
                [$header, $body, $footer] = $this->buildTermParts($template, $oppId, $regId, $regNumber, $currentNumber);

                // Gera PDF
                $pdf = PDF::loadView('pdf.term', compact('header', 'body', 'footer'))
                    ->setPaper('A4', 'portrait');

                // Nome do arquivo
                $agentName = DB::connection('pgsql_remote')
                    ->table('agent')
                    ->where('id', $regInfo->agent_id)
                    ->value('name');
                
                $agentSlug = Str::slug($agentName ?: '');
                $filename = "term_{$oppId}_{$regNumber}_{$agentSlug}.pdf";

                // Salva PDF
                $pdf->save("{$termsPath}/{$filename}");

                // Registra no banco
                GeneratedTerm::create([
                    'template_id' => $template->id,
                    'opportunity_id' => $oppId,
                    'registration_id' => $regId,
                    'filename' => $filename,
                    'sequence_number' => $currentNumber,
                ]);

                $files[] = "{$termsPath}/{$filename}";
                $currentNumber++;

                // Atualiza progresso
                $process->incrementProcessed();

            } catch (\Exception $e) {
                Log::error('Erro ao gerar termo individual', [
                    'process_id' => $this->processId,
                    'registration_id' => $regId,
                    'error' => $e->getMessage()
                ]);
                // Continua com as próximas inscrições
            }
        }

        // Atualiza sequência
        if ($setting->last_sequence !== $currentNumber - 1) {
            $setting->last_sequence = $currentNumber - 1;
            $setting->save();
        }

        // Cria ZIP se houver arquivos
        $zipFilename = null;
        if (!empty($files)) {
            $zipName = "terms_{$oppId}_" . date('Y-m-d_H-i-s') . ".zip";
            $zipPath = "{$termsPath}/{$zipName}";
            
            $zip = new \ZipArchive;
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                foreach ($files as $file) {
                    if (file_exists($file)) {
                        $zip->addFile($file, basename($file));
                    }
                }
                $zip->close();
                $zipFilename = $zipName;
            }
        }

        $process->markAsCompleted($zipFilename);
    }

    protected function buildTermParts(Template $tpl, int $oppId, int $regId, string $regNumber, int $sequenceNumber): array
    {
        // Código idêntico ao método original do controller
        // (copiando toda a lógica existente)
        
        // --- 0) Identifica todas as fases relevantes (pai + filhas, exceto next) ---
        $phaseIds = ExternalOpportunity::query()
            ->where(fn($q) => $q->where('id', $oppId)->orWhere('parent_id', $oppId))
            ->where('id', '!=', $oppId + 1)
            ->pluck('id')
            ->toArray();

        // --- 1) Busca todos os registration_ids filhos + pai em que buscar metas ---
        // pai é $regId
        $regMap = [$oppId => $regId];

        // para cada filha, procura registration_meta.previousPhaseRegistrationId = $regId
        $childRows = DB::connection('pgsql_remote')
            ->table('registration as r')
            ->join('registration_meta as rm', 'r.id', '=', 'rm.object_id')
            ->whereIn('r.opportunity_id', $phaseIds)
            ->where('rm.key', 'previousPhaseRegistrationId')
            ->where('rm.value', (string) $regId)
            ->select('r.id', 'r.opportunity_id')
            ->get();
        
        foreach ($childRows as $row) {
            $regMap[$row->opportunity_id] = $row->id;
        }

        // --- 2) Carrega metas para cada registration_id ---
        $metaCache = [];
        foreach ($regMap as $phaseId => $rId) {
            $metaCache[$phaseId] = DB::connection('pgsql_remote')
                ->table('registration_meta')
                ->where('object_id', $rId)
                ->pluck('value', 'key')
                ->toArray();
        }

        // --- 3) Carrega inscription para 'range' e 'agent' sempre da inscrição principal ($regId) ---
        $ins = DB::connection('pgsql_remote')
            ->table('registration')
            ->where('id', $regId)
            ->select('range', 'agent_id', 'number')
            ->first();

        // --- 4) Carrega registration_ranges da opportunity-pai ---
        $opp = ExternalOpportunity::findOrFail($oppId);
        $parentOpp = $opp->parent_id ? ExternalOpportunity::findOrFail($opp->parent_id) : $opp;
        $ranges = json_decode($parentOpp->registration_ranges ?? '[]', true) ?: [];

        // --- 5) Monta valor + extenso para {{valor}} ---
        $label = $ins->range ?? null;
        $num = null;
        foreach ($ranges as $r) {
            if (($r['label'] ?? '') === $label) {
                $num = $r['value'];
                break;
            }
        }
        
        $valorReplacement = '';
        if ($num !== null) {
            $fmtCur = new NumberFormatter('pt_BR', NumberFormatter::CURRENCY);
            $formatted = $fmtCur->formatCurrency($num, 'BRL');

            $intPart = (int) floor($num);
            $cents = (int) round(($num - $intPart) * 100);

            $fmtSpell = new NumberFormatter('pt_BR', NumberFormatter::SPELLOUT);
            $intSpell = ucfirst($fmtSpell->format($intPart)) . ' reais';

            if ($cents > 0) {
                $centSpell = ucfirst($fmtSpell->format($cents)) . ' centavos';
                $spell = "{$intSpell} e {$centSpell}";
            } else {
                $spell = $intSpell;
            }

            $valorReplacement = "{$formatted} ({$spell})";
        }

        // --- 6) Inicia arrays de busca/substituição com {{valor}} e {{projeto}} ---
        $search = ['{{valor}}', '{{ valor }}'];
        $replace = [$valorReplacement, $valorReplacement];

        // $label já foi definido acima como $ins->range
        $modalidadeReplacement = $label ?: '';

        // adiciona ao array de busca/substituição
        $search[] = '{{modalidade}}';
        $replace[] = $modalidadeReplacement;
        $search[] = '{{ modalidade }}';
        $replace[] = $modalidadeReplacement;

        // placeholder {{inscricao}}
        $search[] = '{{inscricao}}';
        $replace[] = $regNumber;
        $search[] = '{{ inscricao }}';
        $replace[] = $regNumber;

        $projectName = '';
        // tenta coluna direta em registration (caso exista)
        if (isset($ins->project_name)) {
            $projectName = $ins->project_name;
        } else {
            // ou meta remota com key 'projectName' na fase principal
            $projectName = $metaCache[$oppId]['projectName'] ?? '';
        }

        $search[] = '{{projeto}}';
        $replace[] = $projectName;
        $search[] = '{{ projeto }}';
        $replace[] = $projectName;

        // --- 7) Adiciona todos os outros placeholders mapeados ---
        $mappings = PlaceholderMapping::where('opportunity_id', $oppId)
            ->orderBy('priority')->get();
        
        foreach ($mappings as $map) {
            if (in_array($map->placeholder_key, ['valor', 'projeto'], true)) {
                continue;
            }

            // descobre a fase deste field
            $fieldConfig = ExternalRegistrationFieldConfiguration::findOrFail($map->field_id);
            $phaseId = $fieldConfig->opportunity_id;

            $raw = '{{' . $map->placeholder_key . '}}';
            $spaced = '{{ ' . $map->placeholder_key . ' }}';

            // busca no cache de metas da fase certa
            $value = $metaCache[$phaseId]['field_' . $map->field_id] ?? '';

            // se ainda vazio, tenta no agente
            if ($value === '') {
                $agent = DB::connection('pgsql_remote')
                    ->table('agent')
                    ->where('id', $ins->agent_id)
                    ->first();
                $value = $agent->{$map->placeholder_key} ?? '';
            }

            $search[] = $raw;
            $replace[] = $value;
            $search[] = $spaced;
            $replace[] = $value;
        }

        // --- 8) Substitui {{id}} usando o $sequenceNumber recebido ---
        $idVal = (string) $sequenceNumber;
        $search[] = '{{id}}';
        $replace[] = $idVal;
        $search[] = '{{ id }}';
        $replace[] = $idVal;

        // --- 9) Aplica em header, body e footer separadamente ---
        $header = str_replace($search, $replace, $tpl->header_html);
        $body = str_replace($search, $replace, $tpl->body_html);
        $footer = str_replace($search, $replace, $tpl->footer_html);

        return [$header, $body, $footer];
    }
}
