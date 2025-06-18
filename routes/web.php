<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\PlaceholderMappingController;

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

    Route::get('api/fields/{parentId}', function($parentId) {
        // Monta lista de fases relevantes (pai + filhos exceto parent+1)
        $phaseIds = \App\Models\ExternalOpportunity::query()
        ->where(function($q) use($parentId){
            $q->where('id',$parentId)
            ->orWhere('parent_id',$parentId);
        })
        ->where('id','!=',$parentId+1)
        ->pluck('id')
        ->toArray();

        return \App\Models\ExternalRegistrationFieldConfiguration::query()
        ->whereIn('opportunity_id',$phaseIds)
        ->orderBy('opportunity_id')
        ->orderBy('display_order')
        ->get(['id','title']);
    })
    ->name('admin.api.fields');
});
