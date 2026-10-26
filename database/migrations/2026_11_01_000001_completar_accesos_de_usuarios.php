<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/**
 * Ahora cada módulo tiene su propia casilla de acceso. Para que nadie pierda (ni gane) acceso por el
 * cambio, a los usuarios existentes se les copia lo que ya podían ver:
 *  - Historial de ventas y Presupuestos dependían de "Ventas".
 *  - Metas, Configuraciones y Agente de WhatsApp eran para administradores.
 */
return new class extends Migration
{
    public function up(): void
    {
        User::query()->where('role', '!=', 'superadmin')->each(function (User $u) {
            $a = (array) ($u->accesos ?? []);
            $esAdmin = $u->role === 'administrador';

            $a['historial_ventas'] = (bool) ($a['ventas'] ?? false);
            $a['presupuestos']     = (bool) ($a['ventas'] ?? false);
            $a['metas']            = $esAdmin;
            $a['configuraciones']  = $esAdmin;
            $a['agente_whatsapp']  = $esAdmin;
            $a['whatsapp']         = (bool) ($a['whatsapp'] ?? false);
            $a['reportes']         = (bool) ($a['reportes'] ?? false);

            $u->forceFill(['accesos' => $a])->saveQuietly();
        });
    }

    public function down(): void
    {
        // Los accesos extra son inofensivos: no se revierten
    }
};
