<?php

namespace Tests\Feature;

use App\Filament\Pages\CobrosPage;
use App\Filament\Pages\PosPage;
use App\Filament\Resources\HistorialVentasResource\Pages\ListHistorialVentas;
use App\Models\User;
use App\Services\Caja\CobroInvalido;
use App\Services\Caja\CobrosPendientes;
use App\Support\Modulos;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Concerns\BaseDelphiSqlite;
use Tests\TestCase;

/** Caja → Cobros: el cajero ve las ventas pendientes de cobro (se actualizan solas) y las cobra con las formas de pago. */
class CobrosCajaTest extends TestCase
{
    use BaseDelphiSqlite, RefreshDatabase;

    private CobrosPendientes $caja;
    private User $cajero;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-16 10:30:00'));

        $this->prepararDelphi();
        // las consultas por HTTP abren la base Delphi de la empresa por su nombre: en los tests es la SQLite en memoria
        $this->prepararEmpresa(['modulo_caja_enabled' => false, 'db_name' => config('database.connections.delphi.database')], ['historial_ventas' => true]);
        DB::connection('delphi')->table('personal')->insert(['pernr' => '002', 'pername' => 'Bruno']);
        DB::connection('delphi')->table('cliente')->insert(['custnr' => '0002', 'custname' => 'María Gómez']);
        $this->producto('A1', ['descr' => 'BASE FLUIDA']);

        $this->cajero = User::create([
            'name' => 'Cajera', 'email' => 'caja@test.com', 'password' => bcrypt('x'), 'empresa_id' => $this->empresa->id,
            'role' => 'cajero', 'accesos' => ['cobros' => true],
        ]);
        $this->caja = new CobrosPendientes;
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Venta pendiente de cobro de hoy (flag 4, sin cajanr) con su hora. */
    private function pendiente(string $pernr = '001', int $monto = 100000, string $fecha = '2026-11-16', string $hora = '10:20:00', string $cliente = '0002'): string
    {
        $invnr = $this->venta($pernr, $fecha, [['A1', 'BASE FLUIDA', 1, $monto]]);
        DB::connection('delphi')->table('invo1')->where('invnr', $invnr)->update(['hora' => $hora, 'custnr' => $cliente, 'ctotal' => $monto * 0.4]);

        return $invnr;
    }

    private function fila(string $invnr): object
    {
        return DB::connection('delphi')->table('invo1')->where('invnr', $invnr)->first();
    }

    private function cajaRegs(): int
    {
        return DB::connection('delphi')->table('cajareg')->count();
    }

    // ───────────── Lista de pendientes ─────────────

    public function test_lista_solo_las_ventas_pendientes_de_hoy(): void
    {
        $a = $this->pendiente();
        $cobrada = $this->pendiente();
        DB::connection('delphi')->table('invo1')->where('invnr', $cobrada)->update(['cajanr' => '000000000001']);
        $credito = $this->pendiente();
        DB::connection('delphi')->table('invo1')->where('invnr', $credito)->update(['acredito' => 50000]);
        $noCompletada = $this->venta('001', '2026-11-16', [['A1', 'BASE FLUIDA', 1, 10000]], flag: '2');
        $ayer = $this->pendiente(fecha: '2026-11-15');
        $enCero = $this->venta('001', '2026-11-16', []);

        $r = $this->caja->listar('hoy');

        $this->assertSame([$a], array_column($r['ventas'], 'invnr'));
        $this->assertSame(100000.0, $r['total']);
    }

    public function test_el_periodo_amplia_la_busqueda_a_dias_anteriores(): void
    {
        $hoy = $this->pendiente();
        $hace3 = $this->pendiente(fecha: '2026-11-13');
        $hace22 = $this->pendiente(fecha: '2026-10-25');
        $hace46 = $this->pendiente(fecha: '2026-10-01');

        $ids = fn (string $p) => array_column($this->caja->listar($p)['ventas'], 'invnr');

        $this->assertSame([$hoy], $ids('hoy'));
        $this->assertEqualsCanonicalizing([$hoy, $hace3], $ids('7d'));
        $this->assertEqualsCanonicalizing([$hoy, $hace3, $hace22], $ids('30d'));
        $this->assertNotContains($hace46, $ids('30d'));
    }

    public function test_las_mas_recientes_aparecen_primero(): void
    {
        $primera = $this->pendiente();
        $segunda = $this->pendiente();

        $this->assertSame([$segunda, $primera], array_column($this->caja->listar()['ventas'], 'invnr'));
    }

    public function test_cada_venta_trae_cliente_vendedor_productos_y_hace_cuanto(): void
    {
        $inv = $this->pendiente('002', 90000, hora: '10:25:00');

        $v = $this->caja->listar()['ventas'][0];

        $this->assertSame($inv, $v['invnr']);
        $this->assertSame(str_pad($inv, 6, '0', STR_PAD_LEFT), $v['numero']);
        $this->assertSame(['María Gómez', 'Bruno', 1, 90000.0, '10:25'], [$v['cliente'], $v['vendedor'], $v['lineas'], $v['total'], $v['hora']]);
        $this->assertSame('hace 5 min', $v['hace']);
        $this->assertSame(5, $v['minutos']);
    }

    public function test_sin_cliente_conocido_dice_cliente_ocasional(): void
    {
        $this->pendiente(cliente: '9999');

        $this->assertSame('Cliente ocasional', $this->caja->listar()['ventas'][0]['cliente']);
    }

    public function test_la_busqueda_filtra_por_cliente_vendedor_o_numero(): void
    {
        $a = $this->pendiente('001', 10000, cliente: '0002');   // María Gómez, Vendedora
        $b = $this->pendiente('002', 20000, cliente: '0001');   // OCASIONAL, Bruno

        $this->assertSame([$a], array_column($this->caja->listar('hoy', 'maría')['ventas'], 'invnr'));
        $this->assertSame([$b], array_column($this->caja->listar('hoy', 'bruno')['ventas'], 'invnr'));
        $this->assertSame([$a], array_column($this->caja->listar('hoy', $a)['ventas'], 'invnr'));
        $this->assertSame([], $this->caja->listar('hoy', 'nadie')['ventas']);
    }

    public function test_la_lista_informa_la_ultima_venta_para_avisar_cuando_entra_una_nueva(): void
    {
        $this->assertSame('', $this->caja->listar()['ultima']);

        $a = $this->pendiente();
        $ultimaA = $this->caja->listar()['ultima'];
        $b = $this->pendiente();

        $this->assertGreaterThan((int) $ultimaA, (int) $this->caja->listar()['ultima']);
    }

    public function test_el_detalle_de_la_venta_trae_sus_productos(): void
    {
        $inv = $this->venta('001', '2026-11-16', [['A1', 'BASE FLUIDA', 2, 30000], ['A1', 'LABIAL', 1, 15000]]);

        $v = $this->caja->venta($inv);

        $this->assertSame(75000.0, $v['total']);
        $this->assertCount(2, $v['lineas']);
        $this->assertSame(['BASE FLUIDA', 2.0, 30000.0, 60000.0], [$v['lineas'][0]['descr'], $v['lineas'][0]['qty'], $v['lineas'][0]['precio'], $v['lineas'][0]['subtotal']]);
    }

    public function test_el_detalle_de_una_venta_ya_cobrada_o_inexistente_es_null(): void
    {
        $inv = $this->pendiente();
        $this->caja->cobrar($inv, efectivo: 100000);

        $this->assertNull($this->caja->venta($inv));
        $this->assertNull($this->caja->venta('NO-EXISTE'));
    }

    // ───────────── Cobrar ─────────────

    public function test_cobro_en_efectivo_exacto_registra_cajareg_y_enlaza_la_venta(): void
    {
        $inv = $this->pendiente(monto: 100000);

        $r = $this->caja->cobrar($inv, efectivo: 100000);

        $caja = DB::connection('delphi')->table('cajareg')->first();
        $this->assertSame([100000.0, 0.0, 0.0, 100000.0, 40000.0], [(float) $caja->paygs, (float) $caja->paytr, (float) $caja->paytj, (float) $caja->total, (float) $caja->ctotal]);
        $this->assertSame(['PC', 1, 'GS', '01'], [$caja->op, (int) $caja->flag, $caja->tipo_moneda, $caja->sta_num]);
        $this->assertSame($caja->cajanr, $this->fila($inv)->cajanr);
        $this->assertSame($caja->cajanr, $r['cajanr']);
        $this->assertSame(0.0, $r['vuelto']);
    }

    public function test_efectivo_de_mas_calcula_el_vuelto_y_registra_solo_lo_adeudado(): void
    {
        $inv = $this->pendiente(monto: 85000);

        $r = $this->caja->cobrar($inv, efectivo: 100000);

        $this->assertSame(15000.0, $r['vuelto']);
        $this->assertSame(100000.0, $r['recibido']);
        $this->assertSame(85000.0, (float) DB::connection('delphi')->table('cajareg')->value('paygs'), 'en caja entra lo cobrado, no lo recibido');
    }

    public function test_cobro_totalmente_por_transferencia_o_por_tarjeta(): void
    {
        $a = $this->pendiente(monto: 60000);
        $b = $this->pendiente(monto: 40000);

        $ra = $this->caja->cobrar($a, transferencia: 60000);
        $rb = $this->caja->cobrar($b, tarjeta: 40000);

        $cajas = DB::connection('delphi')->table('cajareg')->orderBy('nro')->get();
        $this->assertSame([0.0, 60000.0, 0.0], [(float) $cajas[0]->paygs, (float) $cajas[0]->paytr, (float) $cajas[0]->paytj]);
        $this->assertSame([0.0, 0.0, 40000.0], [(float) $cajas[1]->paygs, (float) $cajas[1]->paytr, (float) $cajas[1]->paytj]);
        $this->assertSame(['transferencia', 'tarjeta'], [$ra['principal'], $rb['principal']]);
        $this->assertSame(0.0, $ra['vuelto']);
    }

    public function test_cobro_mezclando_formas_de_pago(): void
    {
        $inv = $this->pendiente(monto: 100000);

        $r = $this->caja->cobrar($inv, efectivo: 50000, transferencia: 30000, tarjeta: 20000);

        $caja = DB::connection('delphi')->table('cajareg')->first();
        $this->assertSame([50000.0, 30000.0, 20000.0], [(float) $caja->paygs, (float) $caja->paytr, (float) $caja->paytj]);
        $this->assertSame(1, $this->cajaRegs());
        $this->assertSame('efectivo', $r['principal']);
    }

    public function test_mezcla_con_vuelto_solo_sobre_la_parte_en_efectivo(): void
    {
        $inv = $this->pendiente(monto: 100000);

        $r = $this->caja->cobrar($inv, efectivo: 100000, transferencia: 70000);   // 30.000 en efectivo, entrega 100.000

        $this->assertSame(30000.0, $r['efectivo']);
        $this->assertSame(70000.0, $r['vuelto']);
    }

    public function test_si_los_importes_no_cubren_el_total_no_se_cobra_nada(): void
    {
        $inv = $this->pendiente(monto: 100000);

        try {
            $this->caja->cobrar($inv, efectivo: 60000, transferencia: 20000);
            $this->fail('Debió rechazar el cobro incompleto');
        } catch (CobroInvalido $e) {
            $this->assertStringContainsString('faltan Gs. 20.000', $e->getMessage());
        }

        $this->assertSame(0, $this->cajaRegs());
        $this->assertEmpty($this->fila($inv)->cajanr);
        $this->assertCount(1, $this->caja->listar()['ventas'], 'sigue pendiente');
    }

    public function test_transferencia_y_tarjeta_no_pueden_superar_el_total(): void
    {
        $inv = $this->pendiente(monto: 100000);

        $this->expectException(CobroInvalido::class);
        $this->caja->cobrar($inv, transferencia: 80000, tarjeta: 30000);
    }

    public function test_no_acepta_importes_negativos_ni_cobrar_sin_pagar(): void
    {
        $inv = $this->pendiente(monto: 100000);

        foreach ([['efectivo' => -5], ['efectivo' => 0]] as $args) {
            try {
                $this->caja->cobrar($inv, ...$args);
                $this->fail('Debió rechazar');
            } catch (CobroInvalido) {
                $this->addToAssertionCount(1);
            }
        }
        $this->assertSame(0, $this->cajaRegs());
    }

    public function test_una_venta_no_se_puede_cobrar_dos_veces(): void
    {
        $inv = $this->pendiente(monto: 100000);
        $this->caja->cobrar($inv, efectivo: 100000);

        try {
            $this->caja->cobrar($inv, efectivo: 100000);
            $this->fail('El segundo cobro debió rechazarse');
        } catch (CobroInvalido $e) {
            $this->assertStringContainsString('ya no está pendiente', $e->getMessage());
        }

        $this->assertSame(1, $this->cajaRegs(), 'un solo movimiento de caja');
    }

    public function test_no_se_cobra_una_venta_a_credito_ni_inexistente_ni_no_completada(): void
    {
        $credito = $this->pendiente();
        DB::connection('delphi')->table('invo1')->where('invnr', $credito)->update(['acredito' => 50000]);
        $incompleta = $this->venta('001', '2026-11-16', [['A1', 'X', 1, 5000]], flag: '2');

        foreach ([$credito, $incompleta, 'NO-EXISTE'] as $inv) {
            try {
                $this->caja->cobrar($inv, efectivo: 999999);
                $this->fail("Debió rechazar {$inv}");
            } catch (CobroInvalido) {
                $this->addToAssertionCount(1);
            }
        }
        $this->assertSame(0, $this->cajaRegs());
    }

    public function test_despues_de_cobrar_la_venta_sale_de_la_lista_y_en_el_historial_figura_pagada(): void
    {
        $inv = $this->pendiente(monto: 100000);
        $this->actingAs($this->usuario);
        $historial = fn (string $estado) => Livewire::test(ListHistorialVentas::class)
            ->set('tableFilters.periodo.value', 'mes')->set('tableFilters.estado.value', $estado)->instance()->getTableRecords();

        $this->assertCount(1, $historial('pendiente'));

        $this->caja->cobrar($inv, efectivo: 100000);

        $this->assertSame([], $this->caja->listar()['ventas']);
        $this->assertCount(0, $historial('pendiente'));
        $this->assertCount(1, $historial('pagado'));
    }

    // ───────────── Aviso liviano de cambios ─────────────

    public function test_la_firma_cambia_cuando_entra_una_venta_nueva_y_cuando_se_cobra_una(): void
    {
        $vacia = $this->caja->firma();

        $a = $this->pendiente(monto: 50000);
        $conUna = $this->caja->firma();
        $b = $this->pendiente(monto: 70000);
        $conDos = $this->caja->firma();
        $this->caja->cobrar($a, efectivo: 50000);
        $trasCobrar = $this->caja->firma();

        $this->assertCount(4, array_unique([$vacia, $conUna, $conDos, $trasCobrar]), 'cada cambio produce una huella distinta');
        $this->assertSame($conUna === $this->caja->firma(), false);
        $this->assertSame($this->caja->firma(), $this->caja->firma(), 'sin cambios la huella no varía');
    }

    public function test_la_firma_no_cambia_por_ventas_que_no_son_pendientes_ni_de_otro_periodo(): void
    {
        $this->pendiente();
        $antes = $this->caja->firma('hoy');

        $this->pendiente(fecha: '2026-10-01');                                              // fuera del período
        $this->venta('001', '2026-11-16', [['A1', 'X', 1, 5000]], flag: '2');              // no completada
        $conCaja = $this->pendiente();
        DB::connection('delphi')->table('invo1')->where('invnr', $conCaja)->update(['cajanr' => '000000000009']);

        $this->assertSame($antes, $this->caja->firma('hoy'));
    }

    public function test_el_endpoint_devuelve_solo_la_huella_al_cajero(): void
    {
        $this->pendiente(monto: 123456);
        $this->actingAs($this->cajero);

        $r = $this->getJson(route('caja.cobros.firma'))->assertOk();

        $r->assertJsonStructure(['firma'])->assertJsonCount(1);
        $this->assertMatchesRegularExpression('/^\d+-\d+-\d+$/', $r->json('firma'));
        $this->assertStringNotContainsString('María', $r->getContent(), 'no filtra datos de ventas');
        $this->assertStringContainsString('no-store', (string) $r->headers->get('Cache-Control'));
    }

    public function test_el_endpoint_respeta_el_periodo_pedido(): void
    {
        $this->pendiente();
        $this->pendiente(fecha: '2026-11-14');
        $this->actingAs($this->cajero);

        $hoy = $this->getJson(route('caja.cobros.firma', ['periodo' => 'hoy']))->json('firma');
        $sem = $this->getJson(route('caja.cobros.firma', ['periodo' => '7d']))->json('firma');
        $raro = $this->getJson(route('caja.cobros.firma', ['periodo' => 'cualquier-cosa']))->json('firma');

        $this->assertNotSame($hoy, $sem);
        $this->assertSame($hoy, $raro, 'un período inválido usa "hoy"');
    }

    public function test_el_endpoint_esta_prohibido_sin_el_modulo_y_sin_sesion(): void
    {
        auth()->logout();
        $this->getJson(route('caja.cobros.firma'))->assertUnauthorized();

        $vendedor = User::create([
            'name' => 'Vendedor', 'email' => 'v2@test.com', 'password' => bcrypt('x'), 'empresa_id' => $this->empresa->id,
            'role' => 'vendedor', 'accesos' => ['ventas' => true],
        ]);
        $this->actingAs($vendedor)->getJson(route('caja.cobros.firma'))->assertForbidden();
    }

    // ───────────── Pantalla ─────────────

    public function test_el_cajero_entra_a_cobros_y_el_vendedor_sin_el_modulo_no(): void
    {
        $this->actingAs($this->cajero);
        $this->assertTrue(CobrosPage::canAccess());
        $this->assertTrue(CobrosPage::shouldRegisterNavigation());

        $vendedor = User::create([
            'name' => 'Vendedor', 'email' => 'v@test.com', 'password' => bcrypt('x'), 'empresa_id' => $this->empresa->id,
            'role' => 'vendedor', 'accesos' => ['ventas' => true],
        ]);
        $this->actingAs($vendedor);
        $this->assertFalse(CobrosPage::canAccess());
        Livewire::test(CobrosPage::class)->assertForbidden();
    }

    public function test_los_cajeros_y_administradores_reciben_el_modulo_por_defecto(): void
    {
        $this->assertContains('cobros', Modulos::sugeridos('cajero'));
        $this->assertContains('cobros', Modulos::sugeridos('administrador'));
        $this->assertNotContains('cobros', Modulos::sugeridos('vendedor'));
    }

    public function test_el_cajero_al_entrar_cae_en_los_cobros(): void
    {
        $this->assertSame(CobrosPage::getUrl(), Modulos::inicio($this->cajero));
    }

    public function test_la_pantalla_lista_las_ventas_y_vigila_cambios_cada_2_segundos(): void
    {
        $this->pendiente('001', 120000, cliente: '0002');
        $this->actingAs($this->cajero);

        $t = Livewire::test(CobrosPage::class)
            ->assertSuccessful()
            ->assertSee('Cobros pendientes')
            ->assertSee('María Gómez')
            ->assertSee('Gs. 120.000')
            ->assertSee('Ventas pendientes')
            ->assertSee('En vivo')
            ->assertSee('caja\/cobros\/firma', false)
            ->assertSee('setInterval(() => this.vigilar(), 2000)', false)
            ->assertDontSee('wire:poll', false);

        $this->assertSame(1, $t->get('cantidad'));
    }

    public function test_una_venta_nueva_aparece_en_el_siguiente_refresco_y_cambia_la_ultima(): void
    {
        $this->actingAs($this->cajero);
        $t = Livewire::test(CobrosPage::class)->assertSee('No hay ventas pendientes de cobro');
        $this->assertSame('', $t->get('ultima'));

        $this->pendiente('002', 77000, cliente: '0002');   // el vendedor registra una venta
        $t->call('$refresh')->assertSee('Gs. 77.000')->assertDontSee('No hay ventas pendientes de cobro');

        $this->assertNotSame('', $t->get('ultima'));
        $this->assertSame(1, $t->get('cantidad'));
    }

    public function test_la_busqueda_de_la_pantalla_filtra(): void
    {
        $this->pendiente('001', 11000, cliente: '0002');
        $this->pendiente('002', 22000, cliente: '0001');
        $this->actingAs($this->cajero);

        Livewire::test(CobrosPage::class)->set('buscar', 'bruno')->assertSee('Gs. 22.000')->assertDontSee('Gs. 11.000');
    }

    public function test_abrir_una_venta_muestra_sus_productos_y_el_total(): void
    {
        $inv = $this->venta('001', '2026-11-16', [['A1', 'BASE FLUIDA', 2, 30000]]);
        $this->actingAs($this->cajero);

        Livewire::test(CobrosPage::class)
            ->call('abrirCobro', $inv)
            ->assertSet('seleccion.invnr', $inv)
            ->assertSee('BASE FLUIDA')->assertSee('Total a cobrar')->assertSee('Gs. 60.000')
            ->assertSee('Efectivo recibido')->assertSee('Transferencia')->assertSee('Tarjeta')->assertSee('Confirmar cobro');
    }

    public function test_abrir_una_venta_que_otro_cajero_ya_cobro_avisa_y_no_abre_el_modal(): void
    {
        $inv = $this->pendiente();
        $this->caja->cobrar($inv, efectivo: 100000);
        $this->actingAs($this->cajero);

        Livewire::test(CobrosPage::class)->call('abrirCobro', $inv)->assertSet('seleccion', null);
    }

    public function test_cobrar_desde_la_pantalla_registra_el_cobro_y_muestra_el_resultado(): void
    {
        $inv = $this->pendiente('001', 85000, cliente: '0002');
        $this->actingAs($this->cajero);

        $t = Livewire::test(CobrosPage::class)
            ->call('abrirCobro', $inv)
            ->call('cobrar', ['efectivo' => 100000, 'transferencia' => 0, 'tarjeta' => 0])
            ->assertSet('seleccion', null)
            ->assertSee('Cobro registrado')->assertSee('María Gómez')->assertSee('Vuelto')->assertSee('Gs. 15.000')->assertSee('Imprimir ticket');

        $this->assertNotEmpty($this->fila($inv)->cajanr);
        $this->assertSame(85000.0, (float) DB::connection('delphi')->table('cajareg')->value('paygs'));
        $this->assertStringContainsString("/comprobante/{$inv}/ticket?pago=100000&medio=efectivo", $t->get('cobrado')['ticket']);

        $t->call('cerrarCobrado')->assertSet('cobrado', null)->assertSee('No hay ventas pendientes de cobro');
    }

    public function test_un_cobro_invalido_deja_el_modal_abierto_y_no_guarda(): void
    {
        $inv = $this->pendiente(monto: 100000);
        $this->actingAs($this->cajero);

        Livewire::test(CobrosPage::class)
            ->call('abrirCobro', $inv)
            ->call('cobrar', ['efectivo' => 10000])
            ->assertSet('seleccion.invnr', $inv)
            ->assertSet('cobrado', null);

        $this->assertSame(0, $this->cajaRegs());
    }

    public function test_si_otro_cajero_cobra_mientras_tanto_se_cierra_el_modal_sin_duplicar_el_cobro(): void
    {
        $inv = $this->pendiente(monto: 100000);
        $this->actingAs($this->cajero);

        $t = Livewire::test(CobrosPage::class)->call('abrirCobro', $inv);
        $this->caja->cobrar($inv, efectivo: 100000);                      // lo cobra otro cajero

        $t->call('cobrar', ['efectivo' => 100000])->assertSet('seleccion', null)->assertSet('cobrado', null);

        $this->assertSame(1, $this->cajaRegs());
    }

    public function test_cobrar_sin_venta_seleccionada_no_hace_nada(): void
    {
        $this->actingAs($this->cajero);

        Livewire::test(CobrosPage::class)->call('cobrar', ['efectivo' => 100000])->assertSet('cobrado', null);

        $this->assertSame(0, $this->cajaRegs());
    }

    public function test_si_la_empresa_cobra_en_el_pos_la_pantalla_lo_avisa(): void
    {
        $this->empresa->update(['modulo_caja_enabled' => true]);
        $this->cajero->unsetRelation('empresa');
        $this->actingAs($this->cajero->fresh());

        Livewire::test(CobrosPage::class)->assertSee('cobra directamente en el punto de venta');
    }

    // ───────────── De punta a punta: el vendedor vende, el cajero cobra ─────────────

    public function test_lo_que_vende_el_pos_sin_caja_aparece_en_cobros_y_se_cobra(): void
    {
        $this->empresa->update(['tipo' => 'catalogo']);
        $vendedor = User::create([
            'name' => 'Vendedor', 'email' => 'vend@test.com', 'password' => bcrypt('x'), 'empresa_id' => $this->empresa->id,
            'role' => 'vendedor', 'personal_id' => '001', 'accesos' => ['ventas' => true],
        ]);
        $this->actingAs($vendedor);
        Livewire::test(PosPage::class)->call('agregarDesdeFicha', 'A1', 2)->call('openPaymentModal');

        $this->actingAs($this->cajero);
        $t = Livewire::test(CobrosPage::class)->assertSee('Gs. 20.000');   // A1 cuesta 10.000

        $inv = $this->caja->listar()['ventas'][0]['invnr'];
        $t->call('abrirCobro', $inv)->call('cobrar', ['efectivo' => 10000, 'tarjeta' => 10000])->assertSee('Cobro registrado');

        $caja = DB::connection('delphi')->table('cajareg')->first();
        $this->assertSame([10000.0, 0.0, 10000.0], [(float) $caja->paygs, (float) $caja->paytr, (float) $caja->paytj]);
        $this->assertSame([], $this->caja->listar()['ventas']);
    }
}
