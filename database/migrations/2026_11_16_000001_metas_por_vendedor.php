<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Una meta puede ser de la empresa (personal_id vacío, como hasta ahora) o de un vendedor (personal.pernr de Delphi).
        // Se usa '' y no NULL para que la restricción de unicidad siga valiendo para las metas de la empresa.
        Schema::table('metas', function (Blueprint $table) {
            $table->string('personal_id', 20)->default('')->after('mes');
        });

        Schema::table('metas', function (Blueprint $table) {
            // la clave foránea necesita un índice sobre empresa_id: se le deja uno propio antes de quitar el único viejo
            $table->index('empresa_id', 'metas_empresa_id_idx');
            $table->dropUnique(['empresa_id', 'mes']);
            $table->unique(['empresa_id', 'mes', 'personal_id'], 'metas_empresa_mes_personal_unique');
        });
    }

    public function down(): void
    {
        Schema::table('metas', function (Blueprint $table) {
            $table->dropUnique('metas_empresa_mes_personal_unique');
            $table->unique(['empresa_id', 'mes']);
            $table->dropIndex('metas_empresa_id_idx');
            $table->dropColumn('personal_id');
        });
    }
};
