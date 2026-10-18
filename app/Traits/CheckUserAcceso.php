<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait CheckUserAcceso
{
    /**
     * Verificar si el usuario tiene acceso a un módulo específico
     */
    public static function userHasAcceso($modulo): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Super admin siempre tiene acceso
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Verificar en los accesos del usuario
        $accesos = $user->accesos ?? [];

        return $accesos[$modulo] ?? false;
    }

    /**
     * Verificar si el usuario puede acceder a este recurso
     */
    public static function canAccessResource($modulo): bool
    {
        return self::userHasAcceso($modulo);
    }
}
