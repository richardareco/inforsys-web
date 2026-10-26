<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // El agente solo considera los mensajes posteriores a este id (reinicio de contexto)
        Schema::table('wa_conversaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('contexto_desde_id')->nullable()->after('carrito_at');
        });
    }

    public function down(): void
    {
        Schema::table('wa_conversaciones', fn (Blueprint $table) => $table->dropColumn('contexto_desde_id'));
    }
};
