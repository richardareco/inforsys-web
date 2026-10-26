<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_agente_config', function (Blueprint $table) {
            $table->boolean('confirmacion_unica')->default(true)->after('mayorista_min_unidades');
        });
    }

    public function down(): void
    {
        Schema::table('wa_agente_config', fn (Blueprint $t) => $t->dropColumn('confirmacion_unica'));
    }
};
