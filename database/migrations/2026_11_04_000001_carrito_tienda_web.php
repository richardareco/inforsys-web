<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La tienda puede apagar el carrito (queda solo el botón de consulta por producto)
        Schema::table('ecom_config', function (Blueprint $table) {
            $table->boolean('carrito_activo')->default(true)->after('mostrar_sin_stock');
        });

        // De dónde vino la lista/pedido: chat con el agente o carrito de la tienda web
        Schema::table('wa_conversaciones', function (Blueprint $table) {
            $table->string('carrito_origen', 20)->nullable()->after('carrito_at');
        });
        Schema::table('wa_pedidos', function (Blueprint $table) {
            $table->string('origen', 20)->default('chat')->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('wa_pedidos', fn (Blueprint $t) => $t->dropColumn('origen'));
        Schema::table('wa_conversaciones', fn (Blueprint $t) => $t->dropColumn('carrito_origen'));
        Schema::table('ecom_config', fn (Blueprint $t) => $t->dropColumn('carrito_activo'));
    }
};
