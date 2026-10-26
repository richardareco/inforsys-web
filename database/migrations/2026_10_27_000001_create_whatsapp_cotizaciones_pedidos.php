<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_agente_config', function (Blueprint $table) {
            $table->text('cotizacion_pie')->nullable()->after('regla_mayorista'); // ej. validez, condiciones
        });

        Schema::create('wa_cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('conversacion_id')->nullable()->constrained('wa_conversaciones')->nullOnDelete();
            $table->foreignId('contacto_id')->constrained('wa_contactos')->cascadeOnDelete();
            $table->string('estado', 20)->default('enviada'); // enviada | aceptada | cancelada
            $table->decimal('total', 16, 2)->default(0);
            $table->timestamps();
            $table->index(['empresa_id', 'estado']);
        });

        Schema::create('wa_cotizacion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('wa_cotizaciones')->cascadeOnDelete();
            $table->string('item', 20);
            $table->string('descr', 120);
            $table->decimal('cantidad', 12, 3);
            $table->decimal('precio', 16, 2);
            $table->decimal('subtotal', 16, 2);
        });

        Schema::create('wa_pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('conversacion_id')->nullable()->constrained('wa_conversaciones')->nullOnDelete();
            $table->foreignId('contacto_id')->constrained('wa_contactos')->cascadeOnDelete();
            $table->foreignId('cotizacion_id')->nullable()->unique()->constrained('wa_cotizaciones')->nullOnDelete();
            // borrador (resumen mostrado) | pendiente (cliente confirmó) | aprobado | rechazado | cancelado | convertido
            $table->string('estado', 20)->default('borrador');
            $table->string('nombre_cliente', 120)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('entrega_tipo', 10)->default('retiro'); // retiro | envio
            $table->string('direccion_entrega', 250)->nullable();
            $table->string('forma_pago', 20)->nullable();           // efectivo | transferencia
            $table->boolean('requiere_factura')->default(false);
            $table->string('ruc', 30)->nullable();
            $table->text('observacion')->nullable();
            $table->decimal('total', 16, 2)->default(0);
            $table->unsignedBigInteger('preparado_mensaje_id')->nullable(); // último mensaje del cliente al mostrar el resumen
            $table->timestamp('confirmado_at')->nullable();
            $table->foreignId('gestionado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('gestionado_at')->nullable();
            $table->text('nota_interna')->nullable();
            $table->timestamps();
            $table->index(['empresa_id', 'estado']);
        });

        Schema::create('wa_pedido_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('wa_pedidos')->cascadeOnDelete();
            $table->string('item', 20);
            $table->string('descr', 120);
            $table->decimal('cantidad', 12, 3);
            $table->decimal('precio', 16, 2);
            $table->decimal('subtotal', 16, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_pedido_items');
        Schema::dropIfExists('wa_pedidos');
        Schema::dropIfExists('wa_cotizacion_items');
        Schema::dropIfExists('wa_cotizaciones');
        Schema::table('wa_agente_config', fn (Blueprint $table) => $table->dropColumn('cotizacion_pie'));
    }
};
