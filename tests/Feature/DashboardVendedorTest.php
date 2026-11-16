<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\MetaResource\Pages\CreateMeta;
use App\Filament\Widgets\FinancialStatsWidget;
use App\Filament\Widgets\Vendedor\CategoriasVendedorWidget;
use App\Filament\Widgets\Vendedor\MetaVendedorWidget;
use App\Filament\Widgets\Vendedor\ResumenVendedorWidget;
use App\Filament\Widgets\Vendedor\SinVendedorWidget;
use App\Filament\Widgets\Vendedor\TopProductosVendedorWidget;
use App\Filament\Widgets\Vendedor\VentasDiariasVendedorWidget;
use App\Filament\Widgets\VelocimetroKpiWidget;
use App\Models\Meta;
use App\Models\User;
use App\Services\Dashboard\VentasVendedor;
use App\Support\Modulos;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Concerns\BaseDelphiSqlite;
use Tests\TestCase;

/** Dashboard del vendedor: solo sus ventas, filtrado por mes o rango, con categorías, más vendidos y metas. */
class DashboardVendedorTest extends TestCase
{
    use BaseDelphiSqlite, RefreshDatabase;

    private User $vendedor;
    private VentasVendedor $servicio;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-16 10:00:00'));

        $this->prepararDelphi();
        $this->prepararEmpresa();
        DB::connection('delphi')->table('personal')->insert(['pernr' => '002', 'pername' => 'Otro Vendedor']);

        $this->producto('A1', ['descr' => 'BASE FLUIDA', 'grupnr' => 'MK']);
        $this->producto('A2', ['descr' => 'LABIAL ROJO', 'grupnr' => 'MK']);
        $this->producto('B1', ['descr' => 'CREMA FACIAL', 'grupnr' => 'CP']);
        $this->producto('C1', ['descr' => 'SIN CATEGORIA', 'grupnr' => 'ZZ']);

        $this->vendedor = User::create([
            'name' => 'Vendedora', 'email' => 'vend@test.com', 'password' => bcrypt('x'), 'empresa_id' => $this->empresa->id,
            'role' => 'vendedor', 'personal_id' => '001', 'accesos' => ['dashboard' => true, 'ventas' => true],
        ]);
        $this->servicio = new VentasVendedor('001', $this->empresa->id);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function rangoNoviembre(): array
    {
        return [Carbon::parse('2026-11-01'), Carbon::parse('2026-11-30')];
    }

    /** Ventas de ejemplo: 3 de la vendedora 001 en noviembre, una en octubre, y ruido de otro vendedor y no completadas. */
    private function cargarVentas(): void
    {
        $this->venta('001', '2026-11-03', [['A1', 'BASE FLUIDA', 2, 50000], ['B1', 'CREMA FACIAL', 1, 30000]]);   // 130.000
        $this->venta('001', '2026-11-10', [['A1', 'BASE FLUIDA', 1, 50000]]);                                       // 50.000
        $this->venta('001', '2026-11-12', [['A2', 'LABIAL ROJO', 4, 20000], ['C1', 'SIN CATEGORIA', 1, 10000]]);    // 90.000
        $this->venta('001', '2026-10-20', [['A1', 'BASE FLUIDA', 1, 50000]]);                                       // mes anterior
        $this->venta('002', '2026-11-05', [['A1', 'BASE FLUIDA', 9, 50000]]);                                       // otro vendedor
        $this->venta('001', '2026-11-06', [['A1', 'BASE FLUIDA', 5, 50000]], flag: '2');                            // no completada
    }

    // ───────────── Vendedor vinculado ─────────────

    public function test_se_resuelve_el_vendedor_de_un_usuario_como_lo_hace_el_pos(): void
    {
        $this->assertSame('001', VentasVendedor::paraUsuario($this->vendedor)?->pernr);

        $this->vendedor->personal_id = '1';   // el POS compara con == : '1' y '001' son el mismo vendedor
        $this->assertSame('001', VentasVendedor::paraUsuario($this->vendedor)?->pernr);
    }

    public function test_sin_perfil_de_vendedor_o_sin_vinculo_no_hay_servicio(): void
    {
        $this->assertNull(VentasVendedor::paraUsuario($this->usuario), 'un administrador no tiene panel de vendedor');
        $this->assertNull(VentasVendedor::paraUsuario(null));

        $this->vendedor->personal_id = null;
        $this->assertNull(VentasVendedor::paraUsuario($this->vendedor));

        $this->vendedor->personal_id = '999';   // no existe en personal
        $this->assertNull(VentasVendedor::paraUsuario($this->vendedor));
    }

    // ───────────── Período ─────────────

    public function test_el_periodo_por_defecto_es_el_mes_actual(): void
    {
        [$d, $h, $etiqueta] = VentasVendedor::rango([]);

        $this->assertSame(['2026-11-01', '2026-11-30'], [$d->toDateString(), $h->toDateString()]);
        $this->assertSame('Noviembre 2026', $etiqueta);
    }

    public function test_los_periodos_predefinidos(): void
    {
        $fechas = fn (string $p) => array_map(fn ($c) => $c->toDateString(), array_slice(VentasVendedor::rango(['periodo' => $p]), 0, 2));

        $this->assertSame(['2026-10-01', '2026-10-31'], $fechas('mes_pasado'));
        $this->assertSame(['2026-11-16', '2026-11-16'], $fechas('hoy'));
        $this->assertSame(['2026-11-10', '2026-11-16'], $fechas('7d'));
        $this->assertSame(['2026-10-18', '2026-11-16'], $fechas('30d'));
    }

    public function test_un_rango_personalizado_se_ordena_y_se_limita(): void
    {
        $r = fn (array $f) => array_map(fn ($c) => $c->toDateString(), array_slice(VentasVendedor::rango($f + ['periodo' => 'personalizado']), 0, 2));

        $this->assertSame(['2026-11-02', '2026-11-09'], $r(['desde' => '2026-11-02', 'hasta' => '2026-11-09']));
        $this->assertSame(['2026-11-02', '2026-11-09'], $r(['desde' => '2026-11-09', 'hasta' => '2026-11-02']), 'si vienen al revés se invierten');
        [$d, $h] = VentasVendedor::rango(['periodo' => 'personalizado', 'desde' => '2000-01-01', 'hasta' => '2026-11-09']);
        $this->assertLessThanOrEqual(366, $d->diffInDays($h) + 1, 'el rango máximo es de un año');
        $this->assertSame(['2026-11-01', '2026-11-16'], $r(['desde' => 'basura', 'hasta' => 'basura']), 'fechas inválidas no rompen: se usa el mes hasta hoy');
    }

    public function test_el_periodo_anterior_para_comparar(): void
    {
        [$pd, $ph] = VentasVendedor::anterior(Carbon::parse('2026-11-01'), Carbon::parse('2026-11-30'));
        $this->assertSame(['2026-10-01', '2026-10-16'], [$pd->toDateString(), $ph->toDateString()], 'mes en curso: contra los mismos días del mes anterior');

        [$pd, $ph] = VentasVendedor::anterior(Carbon::parse('2026-11-10'), Carbon::parse('2026-11-16'));
        $this->assertSame(['2026-11-03', '2026-11-09'], [$pd->toDateString(), $ph->toDateString()], '7 días contra los 7 anteriores');
    }

    // ───────────── Datos: solo las ventas del vendedor ─────────────

    public function test_el_resumen_cuenta_solo_las_ventas_completadas_del_vendedor_en_el_rango(): void
    {
        $this->cargarVentas();

        $r = $this->servicio->resumen(...$this->rangoNoviembre());

        $this->assertSame(270000.0, $r['total'], '130.000 + 50.000 + 90.000');
        $this->assertSame(3, $r['cantidad']);
        $this->assertSame(90000.0, $r['ticket']);
        $this->assertSame(9.0, $r['unidades'], '2+1+1+4+1 (sin las del otro vendedor, la no completada ni la de octubre)');
    }

    public function test_un_vendedor_sin_ventas_da_ceros(): void
    {
        $r = (new VentasVendedor('002'))->resumen(...$this->rangoNoviembre());
        $this->assertSame(0, $r['cantidad']);

        $sin = (new VentasVendedor('001'))->resumen(Carbon::parse('2026-01-01'), Carbon::parse('2026-01-31'));
        $this->assertSame(['total' => 0.0, 'cantidad' => 0, 'ticket' => 0.0, 'unidades' => 0.0], $sin);
    }

    public function test_ventas_por_dia_rellena_con_ceros_y_no_pasa_de_hoy(): void
    {
        $this->cargarVentas();

        $s = $this->servicio->porDia(...$this->rangoNoviembre());

        $this->assertCount(16, $s['labels'], 'del 1 al 16 de noviembre (hoy), no los días que faltan');
        $this->assertSame(130000.0, $s['totales'][2]);
        $this->assertSame(0.0, $s['totales'][3]);
        $this->assertSame(50000.0, $s['totales'][9]);
        $this->assertSame(90000.0, $s['totales'][11]);
        $this->assertSame(270000.0, array_sum($s['totales']));
        $this->assertSame(1, $s['cantidades'][2]);
    }

    public function test_un_rango_muy_largo_se_agrupa_por_mes(): void
    {
        $this->cargarVentas();

        $s = $this->servicio->porDia(Carbon::parse('2026-09-01'), Carbon::parse('2026-11-30'));

        $this->assertCount(3, $s['labels']);
        $this->assertSame([0.0, 50000.0, 270000.0], $s['totales']);
    }

    public function test_ventas_por_categoria_con_porcentajes(): void
    {
        $this->cargarVentas();

        $c = $this->servicio->porCategoria(...$this->rangoNoviembre());

        $this->assertSame('Maquillaje', $c[0]['nombre']);
        $this->assertSame(230000.0, $c[0]['monto'], 'A1: 150.000 + A2: 80.000');
        $this->assertSame('Cuidado de la piel', $c[1]['nombre']);
        $this->assertSame(30000.0, $c[1]['monto']);
        $this->assertSame('Sin categoría', $c[2]['nombre']);
        $this->assertEqualsWithDelta(100.0, array_sum(array_column($c, 'pct')), 0.01);
        $this->assertEqualsWithDelta(85.19, $c[0]['pct'], 0.01);
    }

    public function test_los_productos_sin_categoria_valida_se_juntan_en_una_sola_fila(): void
    {
        $this->producto('D1', ['descr' => 'HUERFANO', 'grupnr' => 'YY']);
        $this->venta('001', '2026-11-05', [['C1', 'SIN CATEGORIA', 1, 10000], ['D1', 'HUERFANO', 2, 5000]]);

        $c = $this->servicio->porCategoria(...$this->rangoNoviembre());

        $this->assertCount(1, $c);
        $this->assertSame('Sin categoría', $c[0]['nombre']);
        $this->assertSame(20000.0, $c[0]['monto']);
        $this->assertEqualsWithDelta(100.0, $c[0]['pct'], 0.01);
    }

    public function test_productos_mas_vendidos_ordenados_por_unidades(): void
    {
        $this->cargarVentas();

        $p = $this->servicio->topProductos(...$this->rangoNoviembre());

        $this->assertSame(['A2', 'A1', 'B1', 'C1'], array_column($p, 'codigo'), 'LABIAL 4 u., BASE 3 u. (sin las 9 del otro vendedor)');
        $this->assertSame(4.0, $p[0]['unidades']);
        $this->assertSame(80000.0, $p[0]['monto']);
        $this->assertSame('BASE FLUIDA', $p[1]['nombre']);
        $this->assertSame(150000.0, $p[1]['monto']);
    }

    public function test_los_productos_mas_vendidos_respetan_el_limite(): void
    {
        $this->cargarVentas();

        $this->assertCount(2, $this->servicio->topProductos(...[...$this->rangoNoviembre(), 2]));
    }

    public function test_cambiar_el_rango_cambia_los_resultados(): void
    {
        $this->cargarVentas();

        $oct = $this->servicio->resumen(Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));
        $this->assertSame(50000.0, $oct['total']);

        $dia = $this->servicio->resumen(Carbon::parse('2026-11-10'), Carbon::parse('2026-11-10'));
        $this->assertSame(1, $dia['cantidad']);
        $this->assertSame(50000.0, $dia['total']);
    }

    // ───────────── Metas del vendedor ─────────────

    private function meta(string $personal, float $monto, string $mes = '2026-11-01'): void
    {
        Meta::create(['empresa_id' => $this->empresa->id, 'mes' => $mes, 'personal_id' => $personal, 'monto' => $monto]);
    }

    public function test_la_meta_propia_muestra_el_avance_y_lo_que_falta(): void
    {
        $this->cargarVentas();
        $this->meta('001', 540000);

        $m = $this->servicio->meta(Carbon::parse('2026-11-30'));

        $this->assertSame(540000.0, $m['meta']);
        $this->assertSame(270000.0, $m['vendido']);
        $this->assertSame(50.0, $m['pct']);
        $this->assertSame(270000.0, $m['faltante']);
        $this->assertSame(15, $m['dias_restantes'], 'del 16 al 30 de noviembre');
        $this->assertSame(18000.0, $m['ritmo']);
        $this->assertSame('Noviembre 2026', $m['mes']);
    }

    public function test_sin_meta_propia_no_se_muestra_ninguna(): void
    {
        $this->cargarVentas();

        $this->assertNull($this->servicio->meta(Carbon::parse('2026-11-30')));
    }

    public function test_la_meta_de_la_empresa_o_de_otro_vendedor_no_cuenta_como_propia(): void
    {
        $this->meta('', 9000000);      // meta de toda la empresa
        $this->meta('002', 1000000);   // meta de otro vendedor

        $this->assertNull($this->servicio->meta(Carbon::parse('2026-11-30')));
    }

    public function test_meta_cumplida_y_meta_de_un_mes_ya_cerrado(): void
    {
        $this->cargarVentas();
        $this->meta('001', 200000);
        $this->meta('001', 100000, '2026-10-01');

        $nov = $this->servicio->meta(Carbon::parse('2026-11-30'));
        $this->assertSame(135.0, $nov['pct']);
        $this->assertSame(0.0, $nov['faltante']);
        $this->assertSame(0.0, $nov['ritmo']);

        $oct = $this->servicio->meta(Carbon::parse('2026-10-31'));
        $this->assertSame(50.0, $oct['pct']);
        $this->assertSame(0, $oct['dias_restantes'], 'octubre ya terminó');
        $this->assertSame(50000.0, $oct['faltante']);
    }

    // ───────────── Pantalla ─────────────

    public function test_el_vendedor_ve_solo_sus_widgets_y_no_los_de_la_empresa(): void
    {
        $this->actingAs($this->vendedor);

        $this->assertSame([
            ResumenVendedorWidget::class, MetaVendedorWidget::class, VentasDiariasVendedorWidget::class,
            CategoriasVendedorWidget::class, TopProductosVendedorWidget::class,
        ], (new Dashboard)->getWidgets());
        $acciones = new \ReflectionMethod(Dashboard::class, 'getHeaderActions');
        $this->assertSame([], $acciones->invoke(new Dashboard), 'sin el PDF de la empresa');
    }

    public function test_un_administrador_sigue_viendo_el_dashboard_de_la_empresa(): void
    {
        $widgets = (new Dashboard)->getWidgets();

        $this->assertContains(FinancialStatsWidget::class, $widgets);
        $this->assertNotContains(ResumenVendedorWidget::class, $widgets);
    }

    public function test_un_vendedor_sin_vinculo_ve_el_aviso_y_ningun_dato(): void
    {
        $this->vendedor->update(['personal_id' => null]);
        $this->actingAs($this->vendedor->fresh());

        $this->assertSame([SinVendedorWidget::class], (new Dashboard)->getWidgets());
        Livewire::test(SinVendedorWidget::class)->assertSee('todavía no está vinculado a un vendedor');
    }

    public function test_la_pantalla_del_vendedor_carga_con_su_saludo_y_filtros(): void
    {
        $this->actingAs($this->vendedor);

        Livewire::test(Dashboard::class)
            ->assertSuccessful()
            ->assertSee('Tu panel de ventas')
            ->assertSee('Noviembre 2026')
            ->assertSee('Período');
    }

    public function test_el_widget_resumen_muestra_los_totales_del_vendedor(): void
    {
        $this->cargarVentas();
        $this->actingAs($this->vendedor);

        Livewire::test(ResumenVendedorWidget::class, ['filters' => ['periodo' => 'mes']])
            ->assertSee('Total vendido')->assertSee('Gs. 270.000')
            ->assertSee('Ventas realizadas')->assertSee('Ticket promedio')->assertSee('Gs. 90.000')
            ->assertSee('Unidades vendidas');
    }

    public function test_el_resumen_muestra_la_variacion_si_hubo_ventas_en_el_periodo_anterior(): void
    {
        $this->cargarVentas();
        $this->venta('001', '2026-10-05', [['A1', 'BASE FLUIDA', 1, 50000]]);   // dentro de los primeros 16 días de octubre
        $this->actingAs($this->vendedor);

        Livewire::test(ResumenVendedorWidget::class, ['filters' => ['periodo' => 'mes']])->assertSee('+440,0% vs. período anterior');
    }

    public function test_el_resumen_compara_contra_el_periodo_anterior(): void
    {
        $this->cargarVentas();   // octubre 1-16: sin ventas (la de octubre es del 20)
        $this->actingAs($this->vendedor);

        Livewire::test(ResumenVendedorWidget::class, ['filters' => ['periodo' => 'mes']])->assertSee('Sin ventas en el período anterior');
    }

    public function test_el_resumen_cambia_con_el_rango_elegido(): void
    {
        $this->cargarVentas();
        $this->actingAs($this->vendedor);

        Livewire::test(ResumenVendedorWidget::class, ['filters' => ['periodo' => 'personalizado', 'desde' => '2026-11-10', 'hasta' => '2026-11-10']])
            ->assertSee('Gs. 50.000')->assertDontSee('Gs. 270.000');
        Livewire::test(ResumenVendedorWidget::class, ['filters' => ['periodo' => 'mes_pasado']])->assertSee('Gs. 50.000');
    }

    public function test_los_widgets_no_muestran_nada_a_quien_no_es_vendedor(): void
    {
        $this->cargarVentas();

        foreach ([ResumenVendedorWidget::class, MetaVendedorWidget::class, VentasDiariasVendedorWidget::class, CategoriasVendedorWidget::class, TopProductosVendedorWidget::class] as $w) {
            $this->assertFalse($w::canView(), $w);
        }
    }

    public function test_el_widget_de_categorias_muestra_nombres_montos_y_porcentajes(): void
    {
        $this->cargarVentas();
        $this->actingAs($this->vendedor);

        Livewire::test(CategoriasVendedorWidget::class, ['filters' => ['periodo' => 'mes']])
            ->assertSee('Ventas por categoría')->assertSee('Maquillaje')->assertSee('Gs. 230.000')->assertSee('85,2%')
            ->assertSee('Cuidado de la piel')->assertSee('Sin categoría');
    }

    public function test_el_widget_de_productos_lista_los_mas_vendidos_y_no_los_de_otros(): void
    {
        $this->cargarVentas();
        $this->actingAs($this->vendedor);

        $t = Livewire::test(TopProductosVendedorWidget::class, ['filters' => ['periodo' => 'mes']])
            ->assertSee('Mis productos más vendidos')->assertSee('LABIAL ROJO')->assertSee('BASE FLUIDA');
        $this->assertStringNotContainsString('9 u.', $t->html(), 'las 9 unidades son de otro vendedor');
    }

    public function test_sin_ventas_los_widgets_lo_dicen(): void
    {
        $this->actingAs($this->vendedor);

        Livewire::test(CategoriasVendedorWidget::class, ['filters' => ['periodo' => 'mes']])->assertSee('No hay ventas tuyas en este período');
        Livewire::test(TopProductosVendedorWidget::class, ['filters' => ['periodo' => 'mes']])->assertSee('No hay ventas tuyas en este período');
    }

    public function test_el_widget_de_meta_muestra_el_avance_o_avisa_que_no_hay(): void
    {
        $this->cargarVentas();
        $this->actingAs($this->vendedor);

        Livewire::test(MetaVendedorWidget::class, ['filters' => ['periodo' => 'mes']])->assertSee('Todavía no tenés una meta para Noviembre 2026');

        $this->meta('001', 540000);
        Livewire::test(MetaVendedorWidget::class, ['filters' => ['periodo' => 'mes']])
            ->assertSee('50%')->assertSee('Gs. 540.000')->assertSee('Gs. 270.000')->assertSee('por día');
    }

    public function test_el_grafico_trae_los_datos_del_vendedor(): void
    {
        $this->cargarVentas();
        $this->actingAs($this->vendedor);

        $t = Livewire::test(VentasDiariasVendedorWidget::class, ['filters' => ['periodo' => 'mes']]);
        $metodo = new \ReflectionMethod($t->instance(), 'getData');
        $datos  = $metodo->invoke($t->instance());

        $this->assertSame(270000.0, array_sum($datos['datasets'][0]['data']));
        $this->assertCount(16, $datos['labels']);
    }

    // ───────────── Seguridad ─────────────

    public function test_el_informe_pdf_de_la_empresa_esta_prohibido_para_vendedores(): void
    {
        $this->actingAs($this->vendedor)->get('/dashboard/export-pdf')->assertForbidden();
    }

    public function test_los_vendedores_reciben_el_acceso_al_dashboard_por_defecto(): void
    {
        $this->assertContains('dashboard', Modulos::sugeridos('vendedor'));
    }

    // ───────────── Metas por vendedor (administración) ─────────────

    public function test_se_puede_crear_una_meta_para_un_vendedor(): void
    {
        $admin = User::create([
            'name' => 'Admin 2', 'email' => 'a2@test.com', 'password' => bcrypt('x'), 'empresa_id' => $this->empresa->id,
            'role' => 'administrador', 'accesos' => ['metas' => true],
        ]);
        $this->actingAs($admin);

        Livewire::test(CreateMeta::class)
            ->fillForm(['tipo' => 'vendedor', 'personal_id' => '001', 'mes' => '2026-11-01', 'monto' => 800000])
            ->call('create')
            ->assertHasNoFormErrors();

        $m = Meta::where('personal_id', '001')->firstOrFail();
        $this->assertSame($this->empresa->id, $m->empresa_id);
        $this->assertEquals(800000, $m->monto);

        // la misma meta (mes + vendedor) no se puede duplicar, pero la de la empresa y la de otro vendedor sí
        Livewire::test(CreateMeta::class)->fillForm(['tipo' => 'vendedor', 'personal_id' => '001', 'mes' => '2026-11-01', 'monto' => 1])->call('create');
        $this->assertSame(1, Meta::where('personal_id', '001')->count());

        Livewire::test(CreateMeta::class)->fillForm(['tipo' => 'empresa', 'mes' => '2026-11-01', 'monto' => 5000000])->call('create')->assertHasNoFormErrors();
        Livewire::test(CreateMeta::class)->fillForm(['tipo' => 'vendedor', 'personal_id' => '002', 'mes' => '2026-11-01', 'monto' => 700000])->call('create')->assertHasNoFormErrors();
        $this->assertSame(3, Meta::count());
    }

    public function test_el_velocimetro_de_la_empresa_ignora_las_metas_de_vendedores(): void
    {
        $this->meta('001', 800000);   // solo hay una meta individual
        $this->actingAs($this->usuario);

        $w = Livewire::test(VelocimetroKpiWidget::class);

        $this->assertFalse($w->get('tieneMeta'));
        $this->assertSame(0.0, (float) $w->get('metaMonto'));

        $this->meta('', 3000000);
        $this->assertSame(3000000.0, (float) Livewire::test(VelocimetroKpiWidget::class)->get('metaMonto'));
    }
}
