<?php

use App\Http\Controllers\EventRecordImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EventRecordImportController::class, 'create'])
    ->name('records.import.form');

Route::post('/records/import', [EventRecordImportController::class, 'store'])
    ->name('records.import');
