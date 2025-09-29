<?php

namespace App\Policies;

use App\Models\User;

/**
 * Define autorizaciones relacionadas con operaciones sobre países.
 */
class CountryPolicy
{
    /**
     * Cualquier usuario autenticado puede consultar el listado básico.
     */
    public function viewAny(?User $user): bool
    {
        return $user !== null;
    }

    /**
     * Solo los administradores pueden disparar sincronizaciones externas.
     */
    public function sync(?User $user): bool
    {
        return $user?->isAdmin() ?? false;
    }

    /**
     * Solo los administradores pueden acceder a estadísticas agregadas.
     */
    public function viewStatistics(?User $user): bool
    {
        return $user?->isAdmin() ?? false;
    }
}

