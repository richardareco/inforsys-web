<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Número de WhatsApp (Kapso) asociado a una empresa
        Schema::create('wa_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('phone_number_id', 40)->unique();
            $table->string('nombre', 80)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Configuración del agente (una por empresa)
        Schema::create('wa_agente_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->unique()->constrained('empresas')->cascadeOnDelete();
            $table->boolean('activo')->default(false);
            $table->string('modo', 20)->default('basico'); // basico | agente
            $table->json('depositos')->nullable();          // null = todos
            $table->text('mensaje_bienvenida')->nullable();
            $table->text('horarios')->nullable();
            $table->text('ubicacion')->nullable();
            $table->text('entrega')->nullable();
            $table->boolean('pago_efectivo')->default(true);
            $table->boolean('pago_transferencia')->default(true);
            $table->text('datos_transferencia')->nullable();
            $table->text('tono')->nullable();
            $table->text('instrucciones')->nullable();
            $table->text('regla_mayorista')->nullable();
            $table->unsignedTinyInteger('max_productos')->default(5);
            $table->unsignedTinyInteger('max_fotos')->default(3);
            $table->timestamps();
        });

        Schema::create('wa_contactos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('telefono', 30);
            $table->string('nombre', 120)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->string('ruc', 30)->nullable();
            $table->timestamps();
            $table->unique(['empresa_id', 'telefono']);
        });

        Schema::create('wa_conversaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('linea_id')->constrained('wa_lineas')->cascadeOnDelete();
            $table->foreignId('contacto_id')->constrained('wa_contactos')->cascadeOnDelete();
            $table->string('kapso_conversation_id', 80)->nullable();
            $table->string('estado', 20)->default('agente'); // agente | humano | pausado
            $table->timestamp('ultimo_mensaje_at')->nullable();
            $table->timestamps();
            $table->unique(['linea_id', 'contacto_id']);
        });

        Schema::create('wa_mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversacion_id')->constrained('wa_conversaciones')->cascadeOnDelete();
            $table->string('direccion', 3);                 // in | out
            $table->string('autor', 12)->default('cliente'); // cliente | agente | humano | sistema
            $table->string('tipo', 20)->default('text');
            $table->text('texto')->nullable();
            $table->text('media_url')->nullable();
            $table->string('kapso_message_id', 120)->nullable()->unique();
            $table->string('estado', 20)->default('recibido'); // recibido | enviado | error
            $table->text('error')->nullable();
            $table->timestamps();
            $table->index(['conversacion_id', 'id']);
        });

        // Webhooks recibidos (evita procesar dos veces el mismo evento)
        Schema::create('wa_eventos', function (Blueprint $table) {
            $table->id();
            $table->string('idempotency_key', 120)->unique();
            $table->string('evento', 60)->nullable();
            $table->string('phone_number_id', 40)->nullable();
            $table->string('estado', 20)->default('recibido'); // recibido | procesado | ignorado | error
            $table->text('error')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_eventos');
        Schema::dropIfExists('wa_mensajes');
        Schema::dropIfExists('wa_conversaciones');
        Schema::dropIfExists('wa_contactos');
        Schema::dropIfExists('wa_agente_config');
        Schema::dropIfExists('wa_lineas');
    }
};
