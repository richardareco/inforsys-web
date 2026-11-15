<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Secciones de información que cada empresa quiere agregar a la ficha de un producto ("Garantía", "Cuidados"...).
        // Es una tabla propia de la web (no se toca la base Delphi del cliente), separada por empresa.
        Schema::create('producto_info_extra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('item', 20);               // items.item en la base Delphi de la empresa
            $table->json('secciones')->nullable();    // [{"titulo": "...", "contenido": "..."}]
            $table->timestamps();

            $table->unique(['empresa_id', 'item']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_info_extra');
    }
};
