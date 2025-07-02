<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\PlaceholderMappingController;
use App\Http\Controllers\Admin\TermsController;  // <-- import do TermsController
use App\Http\Controllers\Admin\OpportunitySettingController;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')
     ->name('admin.')
     ->group(function() {

    // ao acessar /admin redireciona para /admin/dashboard
    Route::get('/', function() {
        return redirect()->route('admin.dashboard');
    });

    // Dashboard principal
    Route::get('dashboard', [DashboardController::class, 'index'])
         ->name('dashboard');

    // CRUD de Templates
    Route::resource('templates', TemplateController::class);

    // CRUD de Mappings
    Route::resource('placeholder-mappings', PlaceholderMappingController::class);

    // Form de seleção e geração de Termos
    Route::get('terms/create', [TermsController::class, 'create'])
         ->name('terms.create');
    Route::post('terms', [TermsController::class, 'store'])
         ->name('terms.store');

    // Novas rotas para processamento assíncrono
    Route::get('terms/status', [TermsController::class, 'status'])
         ->name('terms.status');
    Route::get('terms/download', [TermsController::class, 'download'])
         ->name('terms.download');
    Route::get('terms/processes', [TermsController::class, 'processes'])
         ->name('terms.processes');

    // CRUD de configurações de edital (sem create/store manuais)
    Route::resource('opportunity-settings', OpportunitySettingController::class)
        ->except(['create','store','show']);

    // Rota para sincronizar editais externos
    Route::post('opportunity-settings/sync', [OpportunitySettingController::class, 'sync'])
        ->name('opportunity-settings.sync');

    // API: busca campos dinâmicos de um edital + suas fases-filhas
    Route::get('api/fields/{parentId}', function($parentId) {
        $phaseIds = \App\Models\ExternalOpportunity::query()
            ->where(function($q) use($parentId){
                $q->where('id', $parentId)
                  ->orWhere('parent_id', $parentId);
            })
            ->where('id','!=', $parentId + 1)
            ->pluck('id')
            ->toArray();

        return \App\Models\ExternalRegistrationFieldConfiguration::query()
            ->whereIn('opportunity_id', $phaseIds)
            ->orderBy('opportunity_id')
            ->orderBy('display_order')
            ->get(['id','title']);
    })->name('api.fields');
});
