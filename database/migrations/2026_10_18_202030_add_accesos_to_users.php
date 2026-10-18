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
        Schema::table('users', function (Blueprint $table) {
            $table->json('accesos')->nullable()->default(json_encode([
                'dashboard' => true,
                'ventas' => true,
                'clientes' => true,
                'productos' => true,
                'historial_ventas' => false,
                'cierre_caja' => false,
                'cuentas_cobrar' => false,
                'proveedores' => false,
                'compras' => false,
                'gastos' => false,
                'reportes' => false,
                'configuraciones' => false,
                'usuarios' => false,
            ]))->comment('Permisos/accesos del usuario a cada módulo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('accesos');
        });
    }
};
