<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Template;
use App\Models\GeneratedTerm;
use App\Models\PlaceholderMapping;
use App\Models\ExternalOpportunity;
use App\Models\ExternalRegistrationFieldConfiguration;
use Illuminate\Support\Facades\DB;
use PDF; // se usar barryvdh/laravel-dompdf

class TermsController extends Controller
{
    /** Form para escolher Edital + Template */
    public function create()
    {
        $opportunities = ExternalOpportunity::whereNull('parent_id')
            ->where('published_registrations', true)
            ->orderBy('name')->get(['id','name']);
        $templates    = \App\Models\Template::orderBy('name')->get();
        return view('admin.terms.create', compact('opportunities','templates'));
    }

    /** Gera e faz download dos PDFs (zip se quiser em lote) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'opportunity_id' => 'required|integer',
            'template_id'    => 'required|integer',
        ]);

        $oppId     = $data['opportunity_id'];
        $template  = Template::findOrFail($data['template_id']);

        // 1) busca inscrições aprovadas (status=10) na fase principal
        $registrationIds = DB::connection('pgsql_remote')
            ->table('registration')
            ->where('opportunity_id',$oppId)
            ->where('status',10)
            ->pluck('id');

        $files = [];
        foreach ($registrationIds as $regId) {
            // 2) monta HTML do termo
            $html = $this->buildTermHtml($template, $oppId, $regId);

            // 3) gera PDF com Dompdf
            $pdf = PDF::loadHTML($html)
                      ->setPaper('A4','portrait');

            $filename = "term_{$oppId}_{$regId}.pdf";
            // dentro do foreach, antes de gerar o PDF:
            $termsPath = storage_path('app/terms');
            if (! is_dir($termsPath)) {
                mkdir($termsPath, 0755, true);
            }

            // então:
            $pdf->save($termsPath . "/{$filename}");

            // 4) opcional: grava no DB
            GeneratedTerm::create([
                'template_id'     => $template->id,
                'opportunity_id'  => $oppId,
                'registration_id' => $regId,
                'filename'        => $filename,
            ]);

            $files[] = storage_path("app/terms/{$filename}");
        }

        // 5) se for só 1, retorna ele diretamente
        if (count($files) === 1) {
            return response()->download($files[0])->deleteFileAfterSend();
        }

        // 6) senão, zipa todos
        $zipName = "terms_{$oppId}.zip";
        $zipPath = storage_path("app/terms/{$zipName}");
        $zip = new \ZipArchive;
        $zip->open($zipPath, \ZipArchive::CREATE|\ZipArchive::OVERWRITE);
        foreach ($files as $f) {
            $zip->addFile($f, basename($f));
        }
        $zip->close();
        return response()->download($zipPath)->deleteFileAfterSend();
    }

    /**
     * Monta o HTML completo: header + body + footer com placeholders substituídos
     */
    protected function buildTermHtml(Template $tpl, int $oppId, int $regId): string
    {
        // 1) carrega todos os mapeamentos deste edital (oppId), ordenados por prioridade
        $mappings = PlaceholderMapping::where('opportunity_id', $oppId)
            ->orderBy('priority')
            ->get();

        // 2) traz todos os valores de registration_meta para esse regId
        $metas = DB::connection('pgsql_remote')
            ->table('registration_meta')
            ->where('object_id', $regId)
            ->pluck('value', 'key')
            ->toArray();

        // 3) prepara arrays de busca e substituição
        $search  = [];
        $replace = [];

        foreach ($mappings as $map) {
            // busca valor do campo dinâmico
            $fieldKey = "field_{$map->field_id}";
            $val = $metas[$fieldKey] ?? '';

            // monta os dois formatos de placeholder
            $raw    = '{{'.$map->placeholder_key.'}}';
            $spaced = '{{ '.$map->placeholder_key.' }}';

            $search[]  = $raw;
            $replace[] = $val;

            $search[]  = $spaced;
            $replace[] = $val;
        }

        // 4) concatena header, corpo e footer e aplica as substituições
        $fullHtml = $tpl->header_html
                . $tpl->body_html
                . $tpl->footer_html;

        return str_replace($search, $replace, $fullHtml);
    }
}
