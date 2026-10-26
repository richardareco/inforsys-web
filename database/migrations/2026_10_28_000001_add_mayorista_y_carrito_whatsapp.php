<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_agente_config', function (Blueprint $table) {
            $table->boolean('mayorista_activo')->default(false)->after('regla_mayorista');
            $table->unsignedSmallInteger('mayorista_min_unidades')->nullable()->after('mayorista_activo');
        });

        // Productos que el cliente va pidiendo antes de generar la cotización
        Schema::table('wa_conversaciones', function (Blueprint $table) {
            $table->json('carrito')->nullable()->after('estado');
            $table->timestamp('carrito_at')->nullable()->after('carrito');
        });
    }

    public function down(): void
    {
        Schema::table('wa_conversaciones', fn (Blueprint $t) => $t->dropColumn(['carrito', 'carrito_at']));
        Schema::table('wa_agente_config', fn (Blueprint $t) => $t->dropColumn(['mayorista_activo', 'mayorista_min_unidades']));
    }
};
