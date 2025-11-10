<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\EventRecordImportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TemplateDownloadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::post('/register/confirm-two-factor', [RegisteredUserController::class, 'confirmTwoFactor'])
        ->name('register.two-factor.confirm');

    Route::delete('/register/pending-two-factor', [RegisteredUserController::class, 'destroyPending'])
        ->name('register.two-factor.cancel');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'create'])
    ->middleware('guest')
    ->name('two-factor.challenge');

Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])
    ->middleware('guest')
    ->name('two-factor.challenge.store');

Route::middleware('auth')->group(function () {
    // Ruta de ejemplo con Inertia
    Route::get('/chat', function () {
        return Inertia::render('Chat/Index', [
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

    // ============ Admin Panel Routes ============
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/users', function () {
        return Inertia::render('Users/Index');
    })->name('users.index');

    Route::get('/countries', function () {
        return Inertia::render('Countries/Index');
    })->name('countries.index');

    Route::get('/events', function () {
        return Inertia::render('Events/Index');
    })->name('events.index');

    // Route::get('/diagram', function () {
    //     return Inertia::render('Diagram/Index');
    // })->name('diagram.index');

    Route::get('/diagram', function () {
        return Inertia::render('Diagram/Index');
    });

    Route::get('/settings', SettingsController::class)
        ->name('settings.index');

    Route::post('/two-factor', [TwoFactorController::class, 'store'])
        ->name('two-factor.enable');

    Route::post('/two-factor/confirm', [TwoFactorController::class, 'confirm'])
        ->name('two-factor.confirm');

    Route::delete('/two-factor', [TwoFactorController::class, 'destroy'])
        ->name('two-factor.disable');

    Route::post('/two-factor/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])
        ->name('two-factor.recovery-codes');

    Route::get('/', [EventRecordImportController::class, 'create'])
        ->name('records.import.form');

    Route::post('/records/import', [EventRecordImportController::class, 'store'])
        ->name('records.import');

    Route::get('/records/template', TemplateDownloadController::class)
        ->name('records.template');
});
