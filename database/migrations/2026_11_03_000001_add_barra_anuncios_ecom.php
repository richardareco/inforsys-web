<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Barra de anuncios (marquee) arriba de la tienda
        Schema::table('ecom_config', function (Blueprint $table) {
            $table->boolean('anuncio_activo')->default(false)->after('color');
            $table->text('anuncios')->nullable()->after('anuncio_activo');          // un mensaje por línea
            $table->string('anuncio_fondo', 9)->nullable()->after('anuncios');       // null = color de la marca
            $table->string('anuncio_color_texto', 9)->nullable()->after('anuncio_fondo'); // null = automático
        });
    }

    public function down(): void
    {
        Schema::table('ecom_config', fn (Blueprint $t) => $t->dropColumn(['anuncio_activo', 'anuncios', 'anuncio_fondo', 'anuncio_color_texto']));
    }
};
