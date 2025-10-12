<?php

use App\Http\Controllers\EventRecordImportController;
use App\Http\Controllers\TemplateDownloadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Ruta de ejemplo con Inertia
Route::get('/welcome', function () {
    return Inertia::render('Welcome', [
        'laravelVersion' => app()->version(),
    ]);
})->name('welcome');

// Ruta de ejemplo con TypeScript
Route::get('/typescript-example', function () {
    return Inertia::render('TypeScriptExample', [
        'initialCount' => 10,
        'user' => [
            'name' => 'María García',
            'email' => 'maria@example.com',
            'age' => 25
        ]
    ]);
})->name('typescript.example');

Route::get('/', [EventRecordImportController::class, 'create'])
    ->name('records.import.form');

Route::post('/records/import', [EventRecordImportController::class, 'store'])
    ->name('records.import');

Route::get('/records/template', TemplateDownloadController::class)
    ->name('records.template');
