<?php

use App\Http\Controllers\Auth\OktaController;
use App\Http\Controllers\Auth\SamlController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TemplateDownloadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('/login', [OktaController::class, 'login'])
        ->name('login');
});

if (config('saml2_settings.enabled')) {
    Route::prefix('saml')->group(function () {
        Route::get('/login', [SamlController::class, 'login'])->name('saml.login');
        Route::post('/acs', [SamlController::class, 'acs'])->name('saml.acs');
        Route::get('/metadata', [SamlController::class, 'metadata'])->name('saml.metadata');
        Route::get('/sls', [SamlController::class, 'sls'])->name('saml.sls');
    });
}

Route::post('/logout', [OktaController::class, 'logout'])
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

    Route::get('/diagram', function () {
        return Inertia::render('Diagram/Index');
    });

    Route::get('/statistics', function () {
        return Inertia::render('Statistics/Index');
    })->name('users.index');

    Route::get('/charts', function () {
        return Inertia::render('Charts/Index', [
            'pieData' => [
                ['value' => 1048, 'name' => 'Búsquedas'],
                ['value' => 735, 'name' => 'Redes'],
                ['value' => 580, 'name' => 'Email'],
                ['value' => 484, 'name' => 'Referidos'],
                ['value' => 300, 'name' => 'Directo'],
            ],
            'test' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            'lineSeries' => [
                ['name' => 'Tráfico web', 'data' => [120, 132, 101, 134, 90, 230, 210, 240, 260, 230, 250, 270]],
                ['name' => 'App móvil', 'data' => [80, 110, 95, 120, 130, 150, 170, 180, 200, 210, 190, 220]],
            ],
            'barCategories' => ['Q1', 'Q2', 'Q3', 'Q4'],
            'barSeries' => [
                ['name' => 'Ventas', 'data' => [320, 432, 501, 434]],
                ['name' => 'Objetivo', 'data' => [380, 410, 460, 480]],
            ],
            'radarIndicators' => [
                ['name' => 'Disponibilidad', 'max' => 100],
                ['name' => 'Latencia', 'max' => 100],
                ['name' => 'Seguridad', 'max' => 100],
                ['name' => 'Satisfacción', 'max' => 100],
                ['name' => 'Entrega', 'max' => 100],
            ],
            'radarData' => [
                ['name' => 'Planta Norte', 'value' => [92, 85, 78, 88, 82]],
                ['name' => 'Planta Sur', 'value' => [88, 80, 90, 84, 79]],
            ],
            'gaugeValue' => 94.5,
        ]);
    })->name('charts.index');

    Route::get('/charts/echarts-pie', function () {
        return Inertia::render('Charts/EChartsPie', [
            'title' => 'Origen de trafico',
            'data' => [
                ['value' => 1048, 'name' => 'Busquedas'],
                ['value' => 735, 'name' => 'Redes'],
                ['value' => 580, 'name' => 'Email'],
                ['value' => 484, 'name' => 'Referidos'],
                ['value' => 300, 'name' => 'Directo'],
            ],
        ]);
    })->name('charts.echarts.pie');

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
