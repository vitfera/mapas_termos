<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Template;
use App\Models\GeneratedTerm;
use App\Models\PlaceholderMapping;
use App\Models\ExternalOpportunity;
use App\Models\OpportunitySetting;
use App\Models\ExternalRegistrationFieldConfiguration;
use App\Models\TermGenerationProcess;
use App\Jobs\GenerateTermsJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
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
     * Inicia geração assíncrona dos PDFs dos termos
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'opportunity_id' => 'required|integer',
            'template_id'    => 'required|integer',
        ]);

        // Conta quantas inscrições serão processadas
        $registrationCount = DB::connection('pgsql_remote')
            ->table('registration')
            ->where('opportunity_id', $data['opportunity_id'])
            ->where('status', 10)
            ->count();

        if ($registrationCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma inscrição aprovada encontrada para este edital.',
            ], 400);
        }

        // Cria registro do processo
        $process = TermGenerationProcess::create([
            'opportunity_id' => $data['opportunity_id'],
            'template_id' => $data['template_id'],
            'user_id' => null, // Sistema ainda não tem autenticação
            'status' => 'pending',
            'total_registrations' => $registrationCount,
        ]);

        // Despacha job assíncrono
        GenerateTermsJob::dispatch($process->id);

        // Retorna resposta imediata com ID do processo
        return response()->json([
            'success' => true,
            'message' => 'Geração de termos iniciada! O processamento está acontecendo em segundo plano.',
            'process_id' => $process->id,
            'total_registrations' => $registrationCount,
        ]);
    }

    /**
     * Verifica status do processamento
     */
    public function status(Request $request)
    {
        $processId = $request->get('process_id');
        
        if (!$processId) {
            return response()->json(['error' => 'process_id é obrigatório'], 400);
        }

        $process = TermGenerationProcess::find($processId);
        
        if (!$process) {
            return response()->json(['error' => 'Processo não encontrado'], 404);
        }

        return response()->json([
            'status' => $process->status,
            'progress_percentage' => $process->progress_percentage,
            'processed_count' => $process->processed_count,
            'total_registrations' => $process->total_registrations,
            'zip_filename' => $process->zip_filename,
            'error_message' => $process->error_message,
            'started_at' => $process->started_at,
            'completed_at' => $process->completed_at,
        ]);
    }

    /**
     * Download do arquivo ZIP gerado
     */
    public function download(Request $request)
    {
        $processId = $request->get('process_id');
        
        $process = TermGenerationProcess::find($processId);
        
        if (!$process || $process->status !== 'completed' || !$process->zip_filename) {
            return response()->json(['error' => 'Arquivo não disponível'], 404);
        }

        $filePath = storage_path('app/terms/' . $process->zip_filename);
        
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Arquivo não encontrado'], 404);
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Lista processos recentes
     */
    public function processes()
    {
        $processes = TermGenerationProcess::with(['opportunity:id,name', 'template:id,name'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return response()->json($processes);
    }

    /**
     * Gera os arrays [header, body, footer] substituindo placeholders,
     * incluindo o placeholder {{id}} se existir, com numeração sequencial e ano atual.
     */
    protected function buildTermParts(Template $tpl, int $oppId, int $regId, string $regNumber, int $sequenceNumber): array
    {
        // --- 0) Identifica todas as fases relevantes (pai + filhas, exceto next) ---
        $phaseIds = ExternalOpportunity::query()
            ->where(fn($q) => $q->where('id', $oppId)->orWhere('parent_id', $oppId))
            ->where('id', '!=', $oppId + 1)
            ->pluck('id')
            ->toArray();

        // --- 1) Busca todos os registration_ids filhos + pai em que buscar metas ---
        // pai é $regId
        $regMap = [ $oppId => $regId ];

        // para cada filha, procura registration_meta.previousPhaseRegistrationId = $regId
        $childRows = DB::connection('pgsql_remote')
            ->table('registration as r')
            ->join('registration_meta as rm', 'r.id', '=', 'rm.object_id')
            ->whereIn('r.opportunity_id', $phaseIds)
            ->where('rm.key', 'previousPhaseRegistrationId')
            ->where('rm.value', (string) $regId)
            ->select('r.id','r.opportunity_id')
            ->get();
        foreach ($childRows as $row) {
            $regMap[$row->opportunity_id] = $row->id;
        }

        // --- 2) Carrega metas para cada registration_id ---
        $metaCache = [];
        foreach($regMap as $phaseId => $rId) {
            $metaCache[$phaseId] = DB::connection('pgsql_remote')
                ->table('registration_meta')
                ->where('object_id', $rId)
                ->pluck('value','key')
                ->toArray();
        }

        // --- 3) Carrega inscription para 'range' e 'agent' sempre da inscrição principal ($regId) ---
        $ins = DB::connection('pgsql_remote')
            ->table('registration')
            ->where('id', $regId)
            ->select('range','agent_id','number')
            ->first();

        // --- 4) Carrega registration_ranges da opportunity-pai ---
        $opp       = ExternalOpportunity::findOrFail($oppId);
        $parentOpp = $opp->parent_id ? ExternalOpportunity::findOrFail($opp->parent_id) : $opp;
        $ranges    = json_decode($parentOpp->registration_ranges ?? '[]', true) ?: [];

        // --- 5) Monta valor + extenso para {{valor}} ---
        $label = $ins->range ?? null;
        $num   = null;
        foreach($ranges as $r) {
            if(($r['label'] ?? '') === $label) {
                $num = $r['value'];
                break;
            }
        }
        $valorReplacement = '';
        if($num !== null) {
            $fmtCur    = new \NumberFormatter('pt_BR', \NumberFormatter::CURRENCY);
            $formatted = $fmtCur->formatCurrency($num, 'BRL');

            $intPart = (int) floor($num);
            $cents   = (int) round(($num - $intPart) * 100);

            $fmtSpell = new \NumberFormatter('pt_BR', \NumberFormatter::SPELLOUT);
            $intSpell = ucfirst($fmtSpell->format($intPart)) . ' reais';

            if($cents > 0) {
                $centSpell = ucfirst($fmtSpell->format($cents)) . ' centavos';
                $spell     = "{$intSpell} e {$centSpell}";
            } else {
                $spell = $intSpell;
            }

            $valorReplacement = "{$formatted} ({$spell})";
        }

        // --- 6) Inicia arrays de busca/substituição com {{valor}} e {{projeto}} ---
        $search  = ['{{valor}}', '{{ valor }}'];
        $replace = [$valorReplacement, $valorReplacement];

        // $label já foi definido acima como $ins->range
        $modalidadeReplacement = $label ?: '';

        // adiciona ao array de busca/substituição
        $search[]  = '{{modalidade}}';
        $replace[] = $modalidadeReplacement;

        $search[]  = '{{ modalidade }}';
        $replace[] = $modalidadeReplacement;

        // placeholder {{inscricao}}
        $search[]  = '{{inscricao}}';
        $replace[] = $regNumber;
        $search[]  = '{{ inscricao }}';
        $replace[] = $regNumber;

        $projectName = '';
        // tenta coluna direta em registration (caso exista)
        if (isset($ins->project_name)) {
            $projectName = $ins->project_name;
        } else {
            // ou meta remota com key 'projectName' na fase principal
            $projectName = $metaCache[$oppId]['projectName'] ?? '';
        }

        $search[]  = '{{projeto}}';    $replace[] = $projectName;
        $search[]  = '{{ projeto }}';  $replace[] = $projectName;

        // --- 7) Adiciona todos os outros placeholders mapeados ---
        $mappings = PlaceholderMapping::where('opportunity_id', $oppId)
            ->orderBy('priority')->get();
        foreach ($mappings as $map) {
            if (in_array($map->placeholder_key, ['valor','projeto'], true)) {
                continue;
            }

            // descobre a fase deste field
            $fieldConfig = ExternalRegistrationFieldConfiguration::findOrFail($map->field_id);
            $phaseId     = $fieldConfig->opportunity_id;

            $raw    = '{{'.$map->placeholder_key.'}}';
            $spaced = '{{ '.$map->placeholder_key.' }}';

            // busca no cache de metas da fase certa
            $value = $metaCache[$phaseId]['field_'.$map->field_id] ?? '';

            // se ainda vazio, tenta no agente
            if($value === '') {
                $agent = DB::connection('pgsql_remote')
                    ->table('agent')
                    ->where('id', $ins->agent_id)
                    ->first();
                $value = $agent->{$map->placeholder_key} ?? '';
            }

            $search[]  = $raw;    $replace[] = $value;
            $search[]  = $spaced; $replace[] = $value;
        }

        // --- 8) Substitui {{id}} usando o $sequenceNumber recebido ---
        $idVal = (string) $sequenceNumber;
        $search[]  = '{{id}}';
        $replace[] = $idVal;
        $search[]  = '{{ id }}';
        $replace[] = $idVal;

        // --- 9) Aplica em header, body e footer separadamente ---
        $header = str_replace($search, $replace, $tpl->header_html);
        $body   = str_replace($search, $replace, $tpl->body_html);
        $footer = str_replace($search, $replace, $tpl->footer_html);

        return [$header, $body, $footer];
    }
}
