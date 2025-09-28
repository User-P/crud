<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function index(): AnonymousResourceCollection|JsonResponse
    {
        try {
            $users = User::orderBy('created_at', 'desc')->paginate(15);

            return UserResource::collection($users);
        } catch (\Exception $e) {
            Log::error('Error al obtener usuarios: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => 'No se pudieron obtener los usuarios'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            // Hash de la contraseña usando principio de responsabilidad única
            $validatedData['password'] = Hash::make($validatedData['password']);

            $user = User::create($validatedData);

            return (new UserResource($user))
                ->response()
                ->setStatusCode(201)
                ->header('Location', route('user.show', $user));
        } catch (\Exception $e) {
            Log::error('Error al crear usuario: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => 'No se pudo crear el usuario'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return UserResource|JsonResponse
     */
    public function show(User $user): UserResource|JsonResponse
    {
        try {
            return new UserResource($user);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Usuario no encontrado',
                'error' => 'El usuario solicitado no existe'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener usuario: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => 'No se pudo obtener el usuario'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            // Solo hash de la contraseña si se proporciona
            if (isset($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            }

            $user->update($validatedData);

            return (new UserResource($user->fresh()))
                ->response()
                ->setStatusCode(200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Usuario no encontrado',
                'error' => 'El usuario que intenta actualizar no existe'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar usuario: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => 'No se pudo actualizar el usuario'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $userName = $user->name;
            $user->delete();

            return response()->json([
                'message' => "Usuario '{$userName}' eliminado correctamente",
                'data' => null
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Usuario no encontrado',
                'error' => 'El usuario que intenta eliminar no existe'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar usuario: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => 'No se pudo eliminar el usuario'
            ], 500);
        }
    }
}
