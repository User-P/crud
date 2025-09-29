<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'api works';
});

// Rutas de autenticación (públicas)
Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);

    // Rutas de países (API externa)
    Route::get('countries', [CountryController::class, 'index']);
    Route::post('countries/sync', [CountryController::class, 'syncCountries']);
    Route::get('countries/statistics', [CountryController::class, 'statistics']);

    // Rutas de estadísticas (solo admins)
    Route::prefix('statistics')->group(function () {
        Route::get('users', [StatisticsController::class, 'userStatistics']);
        Route::get('activity', [StatisticsController::class, 'activityStatistics']);
        Route::get('dashboard', [StatisticsController::class, 'dashboard']);
        Route::get('historical/{days?}', [StatisticsController::class, 'historicalStatistics'])
            ->where('days', '[0-9]+');
    });
});
