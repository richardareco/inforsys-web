<?php

namespace Tests\Feature;

use App\Filament\Pages\CobrosPage;
use App\Filament\Pages\ConsultaCobrosPage;
use App\Http\Controllers\ComprobantePdfController;
use App\Models\User;
use App\Services\Caja\CobrosPendientes;
use App\Services\Caja\CobrosRealizados;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Concerns\BaseDelphiSqlite;
use Tests\TestCase;

/** Consulta de cobros: la cajera revisa los cobros registrados y reimprime el ticket de cualquiera. */
class ConsultaCobrosTest extends TestCase
{
    use BaseDelphiSqlite, RefreshDatabase;

    private CobrosPendientes $caja;
    private CobrosRealizados $consulta;
    private User $cajero;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-16 15:00:00'));

        $this->prepararDelphi();
        $this->prepararEmpresa(['modulo_caja_enabled' => false, 'db_name' => config('database.connections.delphi.database')]);
        DB::connection('delphi')->table('personal')->insert(['pernr' => '002', 'pername' => 'Bruno']);
        DB::connection('delphi')->table('cliente')->insert(['custnr' => '0002', 'custname' => 'María Gómez']);
        $this->producto('A1', ['descr' => 'BASE FLUIDA']);

        $this->cajero = User::create([
            'name' => 'Cajera', 'email' => 'caja@test.com', 'password' => bcrypt('x'), 'empresa_id' => $this->empresa->id,
            'role' => 'cajero', 'accesos' => ['cobros' => true],
        ]);
        $this->caja     = new CobrosPendientes;
        $this->consulta = new CobrosRealizados;
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Venta cobrada de una vez: devuelve [invnr, cajanr]. */
    private function cobro(int $monto = 100000, array $pagos = ['efectivo' => 100000], string $fecha = '2026-11-16', string $pernr = '001', string $cliente = '0002', string $hora = '14:30:00'): array
    {
        $invnr = $this->venta($pernr, $fecha, [['A1', 'BASE FLUIDA', 1, $monto]]);
        DB::connection('delphi')->table('invo1')->where('invnr', $invnr)->update(['hora' => $hora, 'custnr' => $cliente]);
        $r = $this->caja->cobrar($invnr, $pagos['efectivo'] ?? 0, $pagos['transferencia'] ?? 0, $pagos['tarjeta'] ?? 0);
        DB::connection('delphi')->table('cajareg')->where('cajanr', $r['cajanr'])->update(['fecha' => $fecha]);

        return [$invnr, $r['cajanr']];
    }

    // ───────────── Servicio ─────────────

    public function test_lista_los_cobros_del_dia_con_sus_formas_de_pago(): void
    {
        [$inv, $cajanr] = $this->cobro(100000, ['efectivo' => 40000, 'transferencia' => 60000]);

        $r = $this->consulta->listar('hoy');

        $this->assertCount(1, $r['cobros']);
        $c = $r['cobros'][0];
        $this->assertSame([$cajanr, $inv, 100000.0, 'María Gómez', 'Vendedora', '14:30'], [$c['cajanr'], $c['invnr'], $c['total'], $c['cliente'], $c['vendedor'], $c['hora']]);
        $this->assertSame(['efectivo' => 40000.0, 'transferencia' => 60000.0], $c['pagos']);
        $this->assertSame('transferencia', $c['medio'], 'la forma principal es la de mayor importe');
    }

    public function test_el_resumen_totaliza_por_forma_de_pago(): void
    {
        $this->cobro(100000, ['efectivo' => 100000]);
        $this->cobro(50000, ['transferencia' => 50000]);
        $this->cobro(80000, ['efectivo' => 30000, 'tarjeta' => 50000]);

        $r = $this->consulta->listar('hoy');

        $this->assertSame(3, $r['cantidad']);
        $this->assertSame(230000.0, $r['total']);
        $this->assertSame(['efectivo' => 130000.0, 'transferencia' => 50000.0, 'tarjeta' => 50000.0], $r['por_forma']);
    }

    public function test_los_mas_recientes_aparecen_primero(): void
    {
        [, $primero] = $this->cobro();
        [, $segundo] = $this->cobro();

        $this->assertSame([$segundo, $primero], array_column($this->consulta->listar()['cobros'], 'cajanr'));
    }

    public function test_el_periodo_define_que_cobros_se_ven(): void
    {
        [, $hoy] = $this->cobro();
        [, $ayer] = $this->cobro(fecha: '2026-11-15');
        [, $hace5] = $this->cobro(fecha: '2026-11-11');
        [, $viejo] = $this->cobro(fecha: '2026-10-02');

        $ids = fn (string $p) => array_column($this->consulta->listar($p)['cobros'], 'cajanr');

        $this->assertSame([$hoy], $ids('hoy'));
        $this->assertSame([$ayer], $ids('ayer'));
        $this->assertEqualsCanonicalizing([$hoy, $ayer, $hace5], $ids('7d'));
        $this->assertEqualsCanonicalizing([$hoy, $ayer, $hace5], $ids('mes'));
        $this->assertNotContains($viejo, $ids('30d'));
    }

    public function test_filtra_por_forma_de_pago(): void
    {
        [, $ef] = $this->cobro(10000, ['efectivo' => 10000]);
        [, $tr] = $this->cobro(20000, ['transferencia' => 20000]);
        [, $mx] = $this->cobro(30000, ['efectivo' => 10000, 'tarjeta' => 20000]);

        $ids = fn (string $f) => array_column($this->consulta->listar('hoy', '', $f)['cobros'], 'cajanr');

        $this->assertEqualsCanonicalizing([$ef, $mx], $ids('efectivo'));
        $this->assertSame([$tr], $ids('transferencia'));
        $this->assertSame([$mx], $ids('tarjeta'));
    }

    public function test_la_busqueda_por_cliente_vendedor_venta_o_cobro(): void
    {
        [$inv1, $caja1] = $this->cobro(10000, cliente: '0002', pernr: '001');
        [$inv2, $caja2] = $this->cobro(20000, cliente: '0001', pernr: '002');

        $ids = fn (string $b) => array_column($this->consulta->listar('hoy', $b)['cobros'], 'cajanr');

        $this->assertSame([$caja1], $ids('maría'));
        $this->assertSame([$caja2], $ids('bruno'));
        $this->assertSame([$caja1], $ids($inv1));
        $this->assertSame([$caja2], $ids($caja2));
        $this->assertSame([], $ids('nadie'));
    }

    public function test_no_lista_ventas_pendientes_ni_devoluciones_ni_ventas_no_completadas(): void
    {
        $this->venta('001', '2026-11-16', [['A1', 'X', 1, 5000]]);                              // pendiente: todavía sin cobro
        [, $devuelto] = $this->cobro(7000);
        DB::connection('delphi')->table('cajareg')->where('cajanr', $devuelto)->update(['es_devolucion' => 1]);
        [$anulada, ] = $this->cobro(8000);
        DB::connection('delphi')->table('invo1')->where('invnr', $anulada)->update(['flag' => 'c']);
        [, $valido] = $this->cobro(9000);

        $this->assertSame([$valido], array_column($this->consulta->listar()['cobros'], 'cajanr'));
    }

    public function test_incluye_los_cobros_del_pos_con_modulo_de_caja(): void
    {
        // venta cobrada por el POS: cajareg propio con cajanr enlazado, fuera del flujo de Cobros
        $inv = $this->venta('001', '2026-11-16', [['A1', 'X', 1, 25000]]);
        DB::connection('delphi')->table('cajareg')->insert(['fecha' => '2026-11-16', 'total' => 25000, 'paygs' => 25000, 'op' => 'PC', 'flag' => 1]);
        $cajanr = (string) DB::connection('delphi')->table('cajareg')->value('cajanr');   // lo genera el trigger
        DB::connection('delphi')->table('invo1')->where('invnr', $inv)->update(['cajanr' => $cajanr]);

        $this->assertSame([$cajanr], array_column($this->consulta->listar()['cobros'], 'cajanr'));
    }

    public function test_sin_cliente_conocido_dice_cliente_ocasional(): void
    {
        $this->cobro(cliente: '9999');

        $this->assertSame('Cliente ocasional', $this->consulta->listar()['cobros'][0]['cliente']);
    }

    public function test_muestra_los_cheques_y_otros_como_otros(): void
    {
        [, $cajanr] = $this->cobro(10000);
        DB::connection('delphi')->table('cajareg')->where('cajanr', $cajanr)->update(['paygs' => 0, 'paycq' => 6000, 'payot' => 4000]);

        $this->assertSame(['otros' => 10000.0], $this->consulta->listar()['cobros'][0]['pagos']);
    }

    public function test_el_periodo_no_valido_cae_en_hoy_en_la_pantalla_y_el_rango_es_correcto(): void
    {
        [$d, $h] = CobrosRealizados::rango('mes', Carbon::parse('2026-11-16'));
        $this->assertSame(['2026-11-01', '2026-11-16'], [$d->toDateString(), $h->toDateString()]);
        [$d, $h] = CobrosRealizados::rango('cualquiera', Carbon::parse('2026-11-16'));
        $this->assertSame(['2026-11-16', '2026-11-16'], [$d->toDateString(), $h->toDateString()]);
    }

    // ───────────── Pantalla ─────────────

    public function test_la_cajera_entra_y_ve_los_cobros_con_el_boton_reimprimir(): void
    {
        [$inv, $cajanr] = $this->cobro(90000, ['efectivo' => 50000, 'tarjeta' => 40000]);
        $this->actingAs($this->cajero);

        Livewire::test(ConsultaCobrosPage::class)
            ->assertSuccessful()
            ->assertSee('Consulta de cobros')
            ->assertSee('María Gómez')
            ->assertSee("COBRO {$cajanr}")
            ->assertSee('Gs. 90.000')
            ->assertSee('Efectivo 50.000')->assertSee('Tarjeta 40.000')
            ->assertSee('Reimprimir')
            ->assertSee("/comprobante/{$inv}/ticket?reimpresion=1", false);
    }

    public function test_los_filtros_de_la_pantalla_funcionan(): void
    {
        $this->cobro(11000, ['efectivo' => 11000], cliente: '0002');
        $this->cobro(22000, ['transferencia' => 22000], cliente: '0001');
        $this->actingAs($this->cajero);

        Livewire::test(ConsultaCobrosPage::class)
            ->set('buscar', 'maría')->assertSee('Gs. 11.000')->assertDontSee('Gs. 22.000')
            ->set('buscar', '')->set('forma', 'transferencia')->assertSee('Gs. 22.000')->assertDontSee('Gs. 11.000')
            ->set('forma', '')->set('periodo', 'ayer')->assertSee('No hay cobros para mostrar');
    }

    public function test_valores_invalidos_de_filtro_no_rompen_la_pantalla(): void
    {
        $this->cobro();
        $this->actingAs($this->cajero);

        Livewire::test(ConsultaCobrosPage::class)->set('periodo', 'inventado')->set('forma', 'inventada')->assertSuccessful()->assertSee('Gs. 100.000');
    }

    public function test_sin_cobros_la_pantalla_lo_dice(): void
    {
        $this->actingAs($this->cajero);

        Livewire::test(ConsultaCobrosPage::class)->assertSee('No hay cobros para mostrar');
    }

    public function test_solo_entra_quien_tiene_el_modulo_de_cobros(): void
    {
        $this->actingAs($this->cajero);
        $this->assertTrue(ConsultaCobrosPage::canAccess());
        $this->assertTrue(ConsultaCobrosPage::shouldRegisterNavigation());

        $vendedor = User::create([
            'name' => 'Vendedor', 'email' => 'v@test.com', 'password' => bcrypt('x'), 'empresa_id' => $this->empresa->id,
            'role' => 'vendedor', 'accesos' => ['ventas' => true],
        ]);
        $this->actingAs($vendedor);
        $this->assertFalse(ConsultaCobrosPage::canAccess());
        Livewire::test(ConsultaCobrosPage::class)->assertForbidden();
    }

    public function test_desde_cobros_hay_un_acceso_a_la_consulta(): void
    {
        $this->actingAs($this->cajero);

        Livewire::test(CobrosPage::class)->assertSee('Consulta de cobros')->assertSee(ConsultaCobrosPage::getUrl(), false);
    }

    // ───────────── Reimpresión del ticket ─────────────

    private function ticket(string $invnr, array $query = ['reimpresion' => 1]): string
    {
        return app(ComprobantePdfController::class)->ticket(Request::create('/x', 'GET', $query), $invnr)->render();
    }

    public function test_el_ticket_reimpreso_de_un_cobro_en_efectivo_muestra_su_forma_de_pago(): void
    {
        [$inv] = $this->cobro(100000, ['efectivo' => 100000]);
        $this->actingAs($this->cajero);

        $html = $this->ticket($inv);

        $this->assertStringContainsString('PAGO: EFECTIVO', $html);
        $this->assertStringNotContainsString('PENDIENTE', $html);
        $this->assertStringNotContainsString('PAGO MIXTO', $html);
        $this->assertStringContainsString('REIMPRESI', strtoupper($html));
    }

    public function test_el_ticket_reimpreso_de_un_cobro_mixto_detalla_cada_forma(): void
    {
        [$inv] = $this->cobro(100000, ['efectivo' => 30000, 'transferencia' => 50000, 'tarjeta' => 20000]);
        $this->actingAs($this->cajero);

        $html = $this->ticket($inv);

        $this->assertStringContainsString('PAGO MIXTO', $html);
        $this->assertMatchesRegularExpression('/EFECTIVO:.*30\.000/s', $html);
        $this->assertMatchesRegularExpression('/TRANSFERENCIA:.*50\.000/s', $html);
        $this->assertMatchesRegularExpression('/TARJETA:.*20\.000/s', $html);
        $this->assertStringNotContainsString('VUELTO', $html, 'al reimprimir no se sabe cuánto entregó el cliente');
    }

    public function test_el_ticket_de_un_cobro_mixto_con_vuelto_calcula_el_vuelto_solo_sobre_el_efectivo(): void
    {
        [$inv] = $this->cobro(100000, ['efectivo' => 100000, 'transferencia' => 70000]);   // 30.000 en efectivo, entregó 100.000
        $this->actingAs($this->cajero);

        $html = $this->ticket($inv, ['pago' => 100000, 'medio' => 'efectivo']);

        $this->assertMatchesRegularExpression('/VUELTO:.*70\.000/s', $html);
    }

    public function test_el_ticket_de_un_cobro_en_tarjeta_o_transferencia_no_se_trata_como_mixto(): void
    {
        [$inv] = $this->cobro(40000, ['tarjeta' => 40000]);
        $this->actingAs($this->cajero);

        $html = $this->ticket($inv);

        $this->assertStringContainsString('PAGO: TARJETA', $html);
        $this->assertStringNotContainsString('PAGO MIXTO', $html);
    }

    public function test_una_venta_todavia_pendiente_se_sigue_imprimiendo_como_pendiente_de_cobro(): void
    {
        $inv = $this->venta('001', '2026-11-16', [['A1', 'X', 1, 5000]]);
        $this->actingAs($this->cajero);

        $this->assertStringContainsString('PENDIENTE DE COBRO', $this->ticket($inv));
    }
}
