<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Para este ejemplo, permitimos ver todos los usuarios
        // En producción, esto debería estar basado en roles/permisos
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, User $model): bool
    {
        // Permitir ver cualquier usuario para este ejemplo
        // En producción, podría limitarse a que solo puedan ver su propio perfil
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        // Para este ejemplo, permitimos crear usuarios (registro público)
        // En producción, esto podría requerir permisos de administrador
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, User $model): bool
    {
        // Para este ejemplo, permitimos actualizar cualquier usuario
        // En producción, típicamente solo el propio usuario o un admin
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, User $model): bool
    {
        // Para este ejemplo, permitimos eliminar usuarios
        // En producción, esto requeriría permisos de administrador
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, User $model): bool
    {
        // Solo admins deberían poder restaurar usuarios
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, User $model): bool
    {
        // Solo super admins deberían poder eliminar permanentemente
        return true;
    }
}
