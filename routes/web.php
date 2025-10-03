<?php

use App\Http\Controllers\EventRecordImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => response()->json(['message' => 'OK']))->name('home');

Route::post('/records/import', [EventRecordImportController::class, 'store'])
    ->name('records.import');
