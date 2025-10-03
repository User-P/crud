<?php

use App\Http\Controllers\EventRecordImportController;
use App\Http\Controllers\TemplateDownloadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EventRecordImportController::class, 'create'])
    ->name('records.import.form');

Route::post('/records/import', [EventRecordImportController::class, 'store'])
    ->name('records.import');

Route::get('/records/template', TemplateDownloadController::class)
    ->name('records.template');
