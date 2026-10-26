<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cómo se presenta el agente en el panel de conversaciones
        Schema::table('wa_agente_config', function (Blueprint $table) {
            $table->string('agente_nombre', 60)->nullable()->after('modo');
            $table->string('agente_avatar', 255)->nullable()->after('agente_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('wa_agente_config', fn (Blueprint $t) => $t->dropColumn(['agente_nombre', 'agente_avatar']));
    }
};
