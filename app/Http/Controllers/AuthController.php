<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrar un nuevo usuario
     */
    public function register(StoreUserRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            // Hash de la contraseña
            $validatedData['password'] = Hash::make($validatedData['password']);

            // Si no se especifica rol, asignar rol de usuario por defecto
            if (!isset($validatedData['role'])) {
                $validatedData['role'] = 'user';
            }

            $user = User::create($validatedData);

            // Crear token de acceso
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error en registro: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => 'No se pudo registrar el usuario'
            ], 500);
        }
    }

    /**
     * Iniciar sesión
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                throw ValidationException::withMessages([
                    'email' => ['Las credenciales proporcionadas son incorrectas.'],
                ]);
            }

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Inicio de sesión exitoso',
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en login: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => 'No se pudo iniciar sesión'
            ], 500);
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Sesión cerrada exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error en logout: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => 'No se pudo cerrar la sesión'
            ], 500);
        }
    }

    /**
     * Obtener usuario autenticado
     */
    public function me(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'user' => new UserResource($request->user())
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo usuario: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => 'No se pudo obtener la información del usuario'
            ], 500);
        }
    }
}
