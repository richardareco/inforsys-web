<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/** Módulo Caja → Cobros: lo reciben los administradores y los cajeros que ya existen. */
return new class extends Migration
{
    public function up(): void
    {
        User::query()->whereIn('role', ['administrador', 'cajero'])->each(function (User $u) {
            $a = (array) ($u->accesos ?? []);
            $a['cobros'] = true;
            $u->forceFill(['accesos' => $a])->saveQuietly();
        });
    }

    public function down(): void
    {
        // El acceso es inofensivo: no se revierte
    }
};
