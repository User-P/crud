<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApiErrorHandler
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);

            // Si la respuesta es de tipo error y no es JSON, convertirla
            if ($response->getStatusCode() >= 400 && !$response->headers->contains('Content-Type', 'application/json')) {
                return $this->convertToJsonError($response, $request);
            }

            return $response;
        } catch (Throwable $exception) {
            // Este catch normalmente no debería ejecutarse ya que tenemos el handler global
            // pero sirve como fallback
            return response()->json([
                'success' => false,
                'message' => 'Error inesperado en la API',
                'error' => [
                    'type' => 'unexpected_error',
                    'details' => app()->environment('production') ? [] : [
                        'exception' => get_class($exception),
                        'message' => $exception->getMessage(),
                    ],
                ],
                'timestamp' => now()->toISOString(),
                'path' => $request->getPathInfo(),
                'method' => $request->method(),
            ], 500);
        }
    }

    /**
     * Convertir respuesta de error a formato JSON
     */
    private function convertToJsonError(Response $response, Request $request): Response
    {
        $statusCode = $response->getStatusCode();

        $errorTypes = [
            400 => 'bad_request',
            401 => 'authentication_required',
            403 => 'authorization_failed',
            404 => 'not_found',
            405 => 'method_not_allowed',
            422 => 'validation_error',
            429 => 'too_many_requests',
            500 => 'internal_server_error',
        ];

        $messages = [
            400 => 'Solicitud incorrecta',
            401 => 'Autenticación requerida',
            403 => 'Acceso denegado',
            404 => 'Recurso no encontrado',
            405 => 'Método no permitido',
            422 => 'Error de validación',
            429 => 'Demasiadas solicitudes',
            500 => 'Error interno del servidor',
        ];

        return response()->json([
            'success' => false,
            'message' => $messages[$statusCode] ?? 'Error desconocido',
            'error' => [
                'type' => $errorTypes[$statusCode] ?? 'unknown_error',
                'details' => [],
            ],
            'timestamp' => now()->toISOString(),
            'path' => $request->getPathInfo(),
            'method' => $request->method(),
        ], $statusCode);
    }
}
