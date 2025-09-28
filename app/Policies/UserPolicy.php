<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Solo administradores pueden consultar el listado completo de usuarios
        return $user?->isAdmin() ?? false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, User $model): bool
    {
        // Los usuarios pueden ver su propio perfil
        // Los admins pueden ver cualquier perfil
        return $user && ($user->id === $model->id || $user->isAdmin());
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        // Solo los administradores pueden crear usuarios desde el panel protegido
        return $user?->isAdmin() ?? false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, User $model): bool
    {
        // Los usuarios pueden actualizar su propio perfil
        // Los admins pueden actualizar cualquier perfil
        return $user && ($user->id === $model->id || $user->isAdmin());
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, User $model): bool
    {
        // Solo los admins pueden eliminar usuarios
        // No pueden eliminarse a sí mismos para evitar problemas
        return $user && $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, User $model): bool
    {
        // Solo admins pueden restaurar usuarios eliminados
        return $user && $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, User $model): bool
    {
        // Solo admins pueden eliminar permanentemente usuarios
        // No pueden eliminarse permanentemente a sí mismos
        return $user && $user->isAdmin() && $user->id !== $model->id;
    }
}
