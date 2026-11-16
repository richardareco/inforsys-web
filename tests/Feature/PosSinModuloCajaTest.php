<?php

namespace Tests\Feature;

use App\Filament\Pages\PosPage;
use App\Filament\Resources\HistorialVentasResource\Pages\ListHistorialVentas;
use App\Http\Controllers\ComprobantePdfController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Concerns\BaseDelphiSqlite;
use Tests\TestCase;

/**
 * Configuración "Módulo de caja" de la empresa. Sin el módulo, "Generar Venta" guarda la venta de inmediato (sin modal
 * de cobro ni registro en caja) y queda pendiente de cobro; con el módulo, COBRAR abre el modal y registra el cobro.
 */
class PosSinModuloCajaTest extends TestCase
{
    use BaseDelphiSqlite, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararDelphi();
        $this->prepararEmpresa(['tipo' => 'catalogo', 'modulo_caja_enabled' => false], ['historial_ventas' => true]);
        $this->producto('P1', ['descr' => 'BASE FLUIDA', 'precio3' => 50000, 'costo' => 20000], stock: 10);
    }

    private function cajaActiva(bool $activa): void
    {
        $this->empresa->update(['modulo_caja_enabled' => $activa]);
        $this->usuario->unsetRelation('empresa');
    }

    private function vender(int $cantidad = 2)
    {
        return Livewire::test(PosPage::class)
            ->call('agregarDesdeFicha', 'P1', $cantidad)
            ->call('openPaymentModal');
    }

    /** Ventas del historial según el filtro de estado (sin filtro de período: son de hoy). */
    private function historial(string $estado)
    {
        return Livewire::test(ListHistorialVentas::class)
            ->set('tableFilters.periodo.value', 'mes')
            ->set('tableFilters.estado.value', $estado)
            ->instance()->getTableRecords();
    }

    private function venta(): object
    {
        return DB::connection('delphi')->table('invo1')->first();
    }

    // ───────────── Sin módulo de caja ─────────────

    public function test_el_boton_dice_generar_venta_y_no_cobrar(): void
    {
        $html = Livewire::test(PosPage::class)->call('agregarDesdeFicha', 'P1', 1)->html();

        $this->assertSame(2, substr_count($html, 'Generar Venta'), 'el botón del panel y el de la barra compacta');
        $this->assertSame(1, substr_count($html, 'COBRAR'), 'solo queda la mención del atajo F10 dentro del script, no un botón');
    }

    public function test_generar_venta_guarda_directo_sin_abrir_el_modal_de_cobro(): void
    {
        $t = $this->vender(2);

        $this->assertSame(1, DB::connection('delphi')->table('invo1')->count());
        $this->assertSame([], $t->get('cart'));
        $t->assertSet('lastTotal', 100000.0);
    }

    public function test_la_venta_no_pasa_por_caja(): void
    {
        $this->vender(2);

        $v = $this->venta();
        $this->assertSame(0, DB::connection('delphi')->table('cajareg')->count(), 'no se registra el cobro en caja');
        $this->assertEmpty($v->cajanr, 'sin cajanr');
        $this->assertEquals(100000, $v->total);
        $this->assertSame('4', (string) $v->flag);
        $this->assertSame('01', $v->sta_num);
    }

    public function test_guarda_el_detalle_y_descuenta_el_stock_igual_que_con_caja(): void
    {
        $this->vender(3);

        $d = DB::connection('delphi');
        $linea = $d->table('invo2')->first();
        $this->assertSame([$this->venta()->invnr, 'P1', 3.0, 50000.0, 20000.0], [$linea->invnr, $linea->item, (float) $linea->qty, (float) $linea->precio, (float) $linea->costo]);
        $this->assertSame(7.0, (float) $d->table('itemsdepo')->where('item', 'P1')->value('qty'));
        $this->assertSame(-3.0, (float) $d->table('items')->where('item', 'P1')->value('stock'), 'items.stock baja la misma cantidad');
        $this->assertEquals(60000, $this->venta()->ctotal, 'costo total 3 x 20.000');
    }

    public function test_el_catalogo_ofrece_el_ticket_y_avisa_que_queda_pendiente_de_cobro(): void
    {
        $t = $this->vender(1);

        $t->assertSet('showPaymentModal', true)
            ->assertSet('lastPaymentMethod', 'pendiente')
            ->assertSee('Venta registrada')
            ->assertSee('Queda pendiente de cobro')
            ->assertSee('¿Desea imprimir el ticket?')
            ->assertSee('medio=pendiente', false);

        $t->call('closePaymentModal')->assertSet('showPaymentModal', false)->assertSet('lastInvoiceNumber', null);
    }

    public function test_en_el_historial_la_venta_aparece_como_pendiente_de_cobro(): void
    {
        $this->vender(1);

        $pendiente = $this->historial('pendiente');
        $pagado    = $this->historial('pagado');

        $this->assertCount(1, $pendiente);
        $this->assertCount(0, $pagado);
    }

    public function test_el_ticket_dice_pendiente_de_cobro_y_no_efectivo(): void
    {
        $this->vender(1);
        $invnr = $this->venta()->invnr;

        $html = app(ComprobantePdfController::class)->ticket(Request::create('/x', 'GET', ['medio' => 'pendiente']), $invnr)->render();
        $this->assertStringContainsString('PAGO: PENDIENTE DE COBRO', $html);
        $this->assertStringNotContainsString('VUELTO', $html);

        // al reimprimir desde el historial (sin indicar el medio) también se deduce
        $reimpreso = app(ComprobantePdfController::class)->ticket(Request::create('/x', 'GET', ['reimpresion' => 1]), $invnr)->render();
        $this->assertStringContainsString('PAGO: PENDIENTE DE COBRO', $reimpreso);
        $this->assertStringNotContainsString('PAGO: EFECTIVO', $reimpreso);
    }

    public function test_funciona_igual_en_los_otros_tipos_de_pos(): void
    {
        foreach (['general', 'supermercado'] as $tipo) {
            DB::connection('delphi')->table('invo1')->delete();
            $this->cambiarTipo($tipo);

            $t = Livewire::test(PosPage::class)->set('searchResults', [(object) [
                'item' => 'P1', 'descr' => 'BASE FLUIDA', 'precio' => 50000, 'costo' => 20000, 'stock_depo' => 100, 'scode' => null, 'scode1' => null,
            ]])->call('addToCart', 'P1')->call('openPaymentModal');

            $this->assertSame(1, DB::connection('delphi')->table('invo1')->count(), $tipo);
            $this->assertSame(0, DB::connection('delphi')->table('cajareg')->count(), $tipo);
            $t->assertSet('cart', []);
        }
    }

    public function test_los_otros_tipos_no_cambian_no_abren_el_aviso_de_ticket(): void
    {
        $this->cambiarTipo('general');

        $t = Livewire::test(PosPage::class)->set('searchResults', [(object) [
            'item' => 'P1', 'descr' => 'BASE FLUIDA', 'precio' => 50000, 'costo' => 20000, 'stock_depo' => 100, 'scode' => null, 'scode1' => null,
        ]])->call('addToCart', 'P1')->call('openPaymentModal');

        $t->assertSet('showPaymentModal', false);
    }

    // ───────────── Con módulo de caja ─────────────

    public function test_con_modulo_de_caja_cobrar_abre_el_modal_y_no_guarda_todavia(): void
    {
        $this->cajaActiva(true);

        $t = $this->vender(2);

        $t->assertSet('showPaymentModal', true);
        $this->assertSame(0, DB::connection('delphi')->table('invo1')->count());
        $this->assertSame(0, substr_count($t->html(), 'Generar Venta'));
        $this->assertGreaterThanOrEqual(3, substr_count($t->html(), 'COBRAR'));
    }

    public function test_con_modulo_de_caja_confirmar_registra_el_cobro_y_queda_pagada(): void
    {
        $this->cajaActiva(true);

        $t = $this->vender(2)->set('paymentMethod', 'transferencia')->call('confirmSale');

        $d = DB::connection('delphi');
        $caja = $d->table('cajareg')->first();
        $this->assertNotNull($caja);
        $this->assertEquals(100000, $caja->paytr);
        $this->assertSame($caja->cajanr, $this->venta()->cajanr, 'la venta queda enlazada a su cobro');
        $t->assertSet('lastPaymentMethod', 'transferencia');

        $this->assertCount(1, $this->historial('pagado'));
        $this->assertCount(0, $this->historial('pendiente'));
    }

    public function test_el_ticket_de_una_venta_cobrada_sigue_mostrando_su_forma_de_pago(): void
    {
        $this->cajaActiva(true);
        $this->vender(1)->set('paymentMethod', 'tarjeta')->call('confirmSale');

        $html = app(ComprobantePdfController::class)->ticket(Request::create('/x', 'GET', ['reimpresion' => 1]), $this->venta()->invnr)->render();

        $this->assertStringContainsString('PAGO: TARJETA', $html);
        $this->assertStringNotContainsString('PENDIENTE', $html);
    }

    public function test_cambiar_la_configuracion_cambia_el_flujo_sin_reiniciar(): void
    {
        $this->cajaActiva(true);
        $this->assertTrue((bool) $this->usuario->fresh()->empresa->modulo_caja_enabled);

        $this->cajaActiva(false);
        $this->vender(1);

        $this->assertSame(0, DB::connection('delphi')->table('cajareg')->count());
        $this->assertSame(1, DB::connection('delphi')->table('invo1')->count());
    }
}
