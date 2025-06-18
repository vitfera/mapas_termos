<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\PlaceholderMappingController;
use App\Http\Controllers\Admin\TermsController;  // <-- import do TermsController

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')
     ->name('admin.')
     ->group(function() {
         
    // CRUD de Templates
    Route::resource('templates', TemplateController::class);

    // CRUD de Mappings
    Route::resource('placeholder-mappings', PlaceholderMappingController::class);

    // Form de seleção e geração de Termos
    Route::get('terms/create', [TermsController::class, 'create'])
         ->name('terms.create');
    Route::post('terms', [TermsController::class, 'store'])
         ->name('terms.store');

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
