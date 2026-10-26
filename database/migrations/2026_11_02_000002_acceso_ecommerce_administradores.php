<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/** El nuevo módulo E-commerce arranca habilitado para los administradores (el resto se asigna en Usuarios → Accesos). */
return new class extends Migration
{
    public function up(): void
    {
        User::query()->where('role', 'administrador')->each(function (User $u) {
            $a = (array) ($u->accesos ?? []);
            $a['ecommerce'] = true;
            $u->forceFill(['accesos' => $a])->saveQuietly();
        });
    }

    public function down(): void
    {
        // Sin cambios: el acceso extra es inofensivo
    }
};
