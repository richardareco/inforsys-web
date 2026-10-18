<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->boolean('permitir_venta_sin_stock')->default(false)->after('modulo_caja_enabled')->comment('Si es true: permite vender aunque no haya stock. Si es false: valida que haya stock disponible.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('permitir_venta_sin_stock');
        });
    }
};
