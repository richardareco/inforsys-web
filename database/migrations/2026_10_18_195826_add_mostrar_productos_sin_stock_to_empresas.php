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
            $table->boolean('mostrar_productos_sin_stock')->default(true)->after('permitir_venta_sin_stock')->comment('Si es true: muestra todos los productos incluso sin stock. Si es false: solo muestra productos con stock disponible.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('mostrar_productos_sin_stock');
        });
    }
};
