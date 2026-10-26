<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_pedidos', function (Blueprint $table) {
            $table->string('venta_invnr', 20)->nullable()->after('estado');   // comprobante generado en el sistema al marcar pagado
            $table->string('cliente_delphi', 20)->nullable()->after('venta_invnr');
            $table->timestamp('pagado_at')->nullable()->after('confirmado_at');
            $table->timestamp('anulado_at')->nullable()->after('pagado_at');
        });

        // Estados simplificados: pendiente -> pagado | anulado
        DB::table('wa_pedidos')->whereIn('estado', ['rechazado', 'cancelado'])->update(['estado' => 'anulado']);
        DB::table('wa_pedidos')->where('estado', 'aprobado')->update(['estado' => 'pendiente']);
        DB::table('wa_pedidos')->where('estado', 'convertido')->update(['estado' => 'pagado']);
    }

    public function down(): void
    {
        Schema::table('wa_pedidos', fn (Blueprint $t) => $t->dropColumn(['venta_invnr', 'cliente_delphi', 'pagado_at', 'anulado_at']));
    }
};
