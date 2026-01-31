<?php

use App\Http\Controllers\Auth\OktaController;
use App\Http\Controllers\Auth\SamlController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TemplateDownloadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('/login', [OktaController::class, 'login'])
        ->name('login');
    Route::post('/login', [OktaController::class, 'authenticate'])
        ->name('login.perform');
    Route::get('/register', [OktaController::class, 'showRegister'])
        ->middleware('saml.disabled')
        ->name('register');
    Route::post('/register', [OktaController::class, 'register'])
        ->middleware('saml.disabled')
        ->name('register.perform');
});

if (config('saml2_settings.enabled')) {
    Route::prefix('saml')->group(function () {
        Route::get('/login', [SamlController::class, 'login'])->name('saml.login');
        Route::post('/acs', [SamlController::class, 'acs'])->name('saml.acs');
        Route::get('/metadata', [SamlController::class, 'metadata'])->name('saml.metadata');
        Route::match(['get', 'post'], '/sls', [SamlController::class, 'sls'])->name('saml.sls');
    });
}


Route::middleware('auth')->group(function () {
    Route::post('/logout', [OktaController::class, 'logout'])
        ->name('logout');
    Route::get('/chat', function () {
        return Inertia::render('Chat/Index', [
            'laravelVersion' => app()->version(),
        ]);
    })->name('welcome');

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

    Route::get('/diagram', function () {
        return Inertia::render('Diagram/Index');
    });

    Route::get('/statistics', function () {
        return Inertia::render('Statistics/Index');
    })->name('statistics.index');

    Route::get('/tables', function () {
        return Inertia::render('Tables/Index');
    })->name('tables.index');

    Route::get('/charts', ChartsController::class)->name('charts.index');

    Route::get('/organization-chart', function () {
        return Inertia::render('OrganizationChart/Index');
    })->name('organization-chart.index');

    Route::get('/settings', SettingsController::class)
        ->name('settings.index');

    Route::get('/records/template', TemplateDownloadController::class)
        ->name('records.template');

    // Ruta de prueba para el componente de organization chart
    Route::get('api/organization-data/endpoints', function () {
        $data = [
            [
                'main' => false,
                'name' => 'AXEL RAMIREZ CHAVEZ',
                'cve' => '19057479',
                'devices' => [],
            ],
            [
                'main' => true,
                'name' => 'LUIS ALBERTO VALENTE ROMERO',
                'cve' => '1140594',
                'devices' => [
                    [
                        'uuid' => '89e72a4d-cc2a-4d72-89a8-0fdbd85f6919',
                        'name' => 'GS3000069D01LE',
                    ],
                    [
                        'uuid' => 'a7056fba-68e1-4bbc-a580-34fa68ab4d28',
                        'name' => 'EKT1231783LAP',
                    ],
                    [
                        'uuid' => 'bc224d19-9f32-4a2a-94e0-208ae800e6aa',
                        'name' => 'GS1814117D01LP',
                    ],
                    [
                        'uuid' => '96097c86-2e6d-4b1c-879e-2bab517b6a5d',
                        'name' => 'GS0082579D01LE',
                    ],
                    [
                        'uuid' => 'eb1cd067-8cc1-4782-b215-af7fb571df31',
                        'name' => 'GS0082567D01LE',
                    ],
                ],
            ],
        ];

        return response()->json($data);
    })->name('organization.data');
});
