<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Datos de la tienda online (catálogo público) de cada empresa
        Schema::create('ecom_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->unique()->constrained('empresas')->cascadeOnDelete();
            $table->boolean('activo')->default(false);
            $table->string('slug', 80)->unique();                 // dirección: /tienda/{slug}

            // Identidad
            $table->string('nombre', 80)->nullable();
            $table->string('eslogan', 160)->nullable();
            $table->text('texto_inicio')->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('portada', 255)->nullable();
            $table->string('color', 9)->default('#4f46e5');

            // Contacto
            $table->string('whatsapp', 30)->nullable();
            $table->string('instagram', 120)->nullable();
            $table->string('facebook', 120)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->text('horarios')->nullable();

            // Qué se muestra
            $table->boolean('mostrar_precios')->default(true);
            $table->boolean('mostrar_mayorista')->default(false);
            $table->unsignedSmallInteger('mayorista_min_unidades')->nullable();
            $table->boolean('mostrar_stock')->default(true);          // etiqueta "Agotado" / "Disponible"
            $table->boolean('mostrar_sin_stock')->default(true);      // ¿se listan los productos sin stock?
            $table->json('categorias_ocultas')->nullable();
            $table->json('depositos')->nullable();                    // null = todos
            $table->unsignedSmallInteger('por_pagina')->default(24);
            $table->string('mensaje_whatsapp', 255)->nullable();      // texto con {producto} y {codigo}

            // Buscadores
            $table->string('seo_titulo', 120)->nullable();
            $table->string('seo_descripcion', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecom_config');
    }
};
