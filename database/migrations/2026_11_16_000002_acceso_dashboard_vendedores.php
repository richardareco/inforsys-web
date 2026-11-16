<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/**
 * Los vendedores ahora tienen su propio dashboard (solo con sus ventas): se les habilita el módulo "Dashboard".
 * Con el rol de vendedor el dashboard nunca muestra datos de la empresa ni de otros vendedores.
 */
return new class extends Migration
{
    public function up(): void
    {
        User::query()->where('role', 'vendedor')->each(function (User $u) {
            $a = (array) ($u->accesos ?? []);
            $a['dashboard'] = true;
            $u->forceFill(['accesos' => $a])->saveQuietly();
        });
    }

    public function down(): void
    {
        // El acceso es inofensivo: no se revierte
    }
};
