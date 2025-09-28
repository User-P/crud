<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiErrorController extends Controller
{
  /**
   * Manejar excepciones y devolver respuestas JSON consistentes
   */
  public static function handleException(Throwable $exception, Request $request): JsonResponse
  {
    // Obtener información del error
    $error = self::getErrorDetails($exception);

    // Log del error para debugging (solo errores 500)
    if ($error['status'] >= 500) {
      Log::error('API Error: ' . $exception->getMessage(), [
        'exception' => get_class($exception),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
        'request' => [
          'url' => $request->fullUrl(),
          'method' => $request->method(),
          'ip' => $request->ip(),
          'user_agent' => $request->userAgent(),
        ]
      ]);
    }

    return response()->json([
      'success' => false,
      'message' => $error['message'],
      'error' => [
        'type' => $error['type'],
        'details' => $error['details'],
      ],
      'timestamp' => now()->toISOString(),
      'path' => $request->getPathInfo(),
      'method' => $request->method(),
    ], $error['status']);
  }

  /**
   * Obtener detalles específicos del error basado en el tipo de excepción
   */
  private static function getErrorDetails(Throwable $exception): array
  {
    return match (get_class($exception)) {
      // Errores de validación (422)
      ValidationException::class => [
        'status' => 422,
        'type' => 'validation_error',
        'message' => 'Los datos proporcionados no son válidos',
        'details' => $exception instanceof ValidationException ? $exception->errors() : [],
      ],

      // Modelo no encontrado (404)
      ModelNotFoundException::class => [
        'status' => 404,
        'type' => 'resource_not_found',
        'message' => 'El recurso solicitado no existe',
        'details' => [
          'resource' => $exception instanceof ModelNotFoundException ? class_basename($exception->getModel()) : 'Unknown',
        ],
      ],

      // Ruta no encontrada (404)
      NotFoundHttpException::class => [
        'status' => 404,
        'type' => 'endpoint_not_found',
        'message' => 'El endpoint solicitado no existe',
        'details' => [
          'available_methods' => self::getAvailableMethods(),
        ],
      ],

      // Método no permitido (405)
      MethodNotAllowedHttpException::class => [
        'status' => 405,
        'type' => 'method_not_allowed',
        'message' => 'Método HTTP no permitido para este endpoint',
        'details' => [
          'allowed_methods' => $exception instanceof MethodNotAllowedHttpException
            ? ($exception->getHeaders()['Allow'] ?? [])
            : [],
        ],
      ],

      // No autenticado (401)
      AuthenticationException::class => [
        'status' => 401,
        'type' => 'authentication_required',
        'message' => 'Autenticación requerida',
        'details' => [
          'hint' => 'Incluye el token de autenticación en el header Authorization',
        ],
      ],

      // No autorizado (403)
      AuthorizationException::class => [
        'status' => 403,
        'type' => 'authorization_failed',
        'message' => 'No tienes permisos para realizar esta acción',
        'details' => [
          'required_permission' => $exception->getMessage(),
        ],
      ],

      // Error de base de datos (500)
      QueryException::class => [
        'status' => 500,
        'type' => 'database_error',
        'message' => 'Error en la base de datos',
        'details' => [
          'hint' => 'Error interno del servidor, contacta al administrador',
        ],
      ],

      // Errores HTTP genéricos
      HttpException::class => [
        'status' => $exception instanceof HttpException ? $exception->getStatusCode() : 500,
        'type' => 'http_error',
        'message' => $exception->getMessage() ?: ($exception instanceof HttpException
          ? Response::$statusTexts[$exception->getStatusCode()]
          : 'Error HTTP'),
        'details' => [],
      ],

      // Cualquier otro error (500)
      default => [
        'status' => 500,
        'type' => 'internal_server_error',
        'message' => app()->environment('production')
          ? 'Error interno del servidor'
          : $exception->getMessage(),
        'details' => app()->environment('production') ? [] : [
          'exception' => get_class($exception),
          'file' => $exception->getFile(),
          'line' => $exception->getLine(),
        ],
      ],
    };
  }

  /**
   * Obtener métodos disponibles para la API
   */
  private static function getAvailableMethods(): array
  {
    return [
      'auth' => ['POST /api/v1/auth/register', 'POST /api/v1/auth/login', 'POST /api/v1/auth/logout', 'GET /api/v1/auth/me'],
      'users' => ['GET /api/v1/user', 'POST /api/v1/user', 'GET /api/v1/user/{id}', 'PUT /api/v1/user/{id}', 'DELETE /api/v1/user/{id}'],
    ];
  }
}
