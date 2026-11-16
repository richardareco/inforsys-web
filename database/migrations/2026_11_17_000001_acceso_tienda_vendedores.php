<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/**
 * Los vendedores pueden abrir el módulo Tienda online para copiar el enlace y enviárselo a un cliente. No ven la
 * configuración: esa pantalla es solo de administradores.
 */
return new class extends Migration
{
    public function up(): void
    {
        User::query()->where('role', 'vendedor')->each(function (User $u) {
            $a = (array) ($u->accesos ?? []);
            $a['ecommerce'] = true;
            $u->forceFill(['accesos' => $a])->saveQuietly();
        });
    }

    public function down(): void
    {
        // El acceso es inofensivo: no se revierte
    }
};
