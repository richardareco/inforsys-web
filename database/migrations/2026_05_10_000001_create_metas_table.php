<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->date('mes'); // primer día del mes: 2026-05-01
            $table->decimal('monto', 15, 2);
            $table->timestamps();

            $table->unique(['empresa_id', 'mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas');
    }
};
