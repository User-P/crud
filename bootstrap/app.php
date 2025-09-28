<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Aplicar middleware JSON a todas las rutas API
        $middleware->api(append: [
            \App\Http\Middleware\ForceJsonResponse::class,
            \App\Http\Middleware\ApiErrorHandler::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Manejo personalizado de errores para APIs
        $exceptions->render(function (Throwable $exception, $request) {
            // Solo para rutas API
            if ($request->is('api/*')) {
                return \App\Http\Controllers\ApiErrorController::handleException($exception, $request);
            }
        });
    })->create();
