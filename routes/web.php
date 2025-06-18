<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\TemplatePlaceholderController;
use App\Http\Controllers\Admin\PlaceholderMappingController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')
     ->name('admin.')
     ->group(function() {
         
    // CRUD de Templates
    Route::resource('templates', TemplateController::class);
    
    // CRUD de Placeholders
    Route::resource('template-placeholders', TemplatePlaceholderController::class)
         ->parameters(['template-placeholders' => 'templatePlaceholder']);
    
    // CRUD de Mappings
    Route::resource('placeholder-mappings', PlaceholderMappingController::class);
});
