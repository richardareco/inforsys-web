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
            $table->unsignedTinyInteger('decimales_cantidad')->default(0)->comment('Número de decimales a mostrar en cantidad/stock (0-2)');
            $table->unsignedTinyInteger('decimales_precios')->default(2)->comment('Número de decimales a mostrar en precios (0-4)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['decimales_cantidad', 'decimales_precios']);
        });
    }
};
