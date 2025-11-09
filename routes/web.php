<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\EventRecordImportController;
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
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

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

    Route::get('/statistics', function () {
        return Inertia::render('Statistics/Index');
    })->name('statistics.index');

    Route::get('/settings', function () {
        return Inertia::render('Settings/Index');
    })->name('settings.index');

    Route::get('/', [EventRecordImportController::class, 'create'])
        ->name('records.import.form');

    Route::post('/records/import', [EventRecordImportController::class, 'store'])
        ->name('records.import');

    Route::get('/records/template', TemplateDownloadController::class)
        ->name('records.template');
});
