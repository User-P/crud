<?php

namespace App\Http\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

trait HandleValidationErrors
{
  /**
   * Manejar errores de validación para APIs
   * Sobrescribir el método failedValidation para devolver JSON consistente
   */
  protected function failedValidation(Validator $validator): void
  {
    $errors = $validator->errors();

    $response = new JsonResponse([
      'success' => false,
      'message' => 'Los datos proporcionados no son válidos',
      'error' => [
        'type' => 'validation_error',
        'details' => $errors->toArray(),
      ],
      'timestamp' => now()->toISOString(),
    ], 422);

    throw new HttpResponseException($response);
  }

  /**
   * Formatear errores de autorización personalizados
   */
  protected function failedAuthorization(): void
  {
    $response = new JsonResponse([
      'success' => false,
      'message' => 'No tienes permisos para realizar esta acción',
      'error' => [
        'type' => 'authorization_failed',
        'details' => [
          'hint' => 'Verifica que tengas los permisos necesarios o que estés autenticado correctamente',
        ],
      ],
      'timestamp' => now()->toISOString(),
    ], 403);

    throw new HttpResponseException($response);
  }
}
