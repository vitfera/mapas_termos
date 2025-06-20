<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::get('/info', function () {
    return response()->json([
        'name' => config('app.name', 'Mapas'),
        'version' => trim(File::get(base_path('version.txt')) ?? 'v1.0.0'),
        'environment' => app()->environment(),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'timezone' => config('app.timezone'),
    ]);
});