<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La tienda online puede mostrar la ficha del producto (marca, beneficios, tonos, secciones extra...)
        Schema::table('ecom_config', function (Blueprint $table) {
            $table->boolean('mostrar_ficha')->default(true)->after('carrito_activo');
        });
    }

    public function down(): void
    {
        Schema::table('ecom_config', fn (Blueprint $t) => $t->dropColumn('mostrar_ficha'));
    }
};
