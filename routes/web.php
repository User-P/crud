<?php

use App\Http\Controllers\UserImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::middleware('auth')->group(function () {
Route::get('/users/import', [UserImportController::class, 'create'])
    ->name('users.import.create');
Route::post('/users/import', [UserImportController::class, 'store'])
    ->name('users.import.store');
// });
