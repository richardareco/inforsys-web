<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\MetaResource\Pages\CreateMeta;
use App\Filament\Resources\MetaResource\Pages\EditMeta;
use App\Filament\Resources\MetaResource\Pages\ListMetas;
use App\Filament\Widgets\FinancialStatsWidget;
use App\Filament\Widgets\Metas\MetasVendedoresWidget;
use App\Filament\Widgets\Metas\ResumenMetasWidget;
use App\Models\Empresa;
use App\Models\Meta;
use App\Models\User;
use App\Services\Dashboard\MetasVendedores;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Concerns\BaseDelphiSqlite;
use Tests\TestCase;

/** Metas de la empresa y por vendedor, y la pestaña "Metas por vendedor" del dashboard administrativo. */
class MetasAdminTest extends TestCase
{
    use BaseDelphiSqlite, RefreshDatabase;

    private MetasVendedores $servicio;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-16 10:00:00'));   // mitad de mes: a esta altura se espera 53,3%

        $this->prepararDelphi();
        $this->prepararEmpresa();
        $this->usuario->update(['accesos' => ['dashboard' => true, 'metas' => true, 'ventas' => true]]);

        DB::connection('delphi')->table('personal')->insert([
            ['pernr' => '002', 'pername' => 'Bruno'],
            ['pernr' => '003', 'pername' => 'Carla'],
            ['pernr' => '004', 'pername' => 'Diego'],
        ]);
        $this->producto('A1', ['descr' => 'BASE']);
        $this->servicio = new MetasVendedores($this->empresa->id);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function meta(string $personal, float $monto, string $mes = '2026-11-01', ?int $empresa = null): void
    {
        Meta::create(['empresa_id' => $empresa ?? $this->empresa->id, 'mes' => $mes, 'personal_id' => $personal, 'monto' => $monto]);
    }

    /** 001 vendió 270.000, 002 150.000, 003 100.000, 004 40.000 (sin meta) y hay 25.000 sin vendedor. */
    private function cargarMes(): void
    {
        $this->venta('001', '2026-11-03', [['A1', 'BASE', 2, 100000]]);
        $this->venta('001', '2026-11-10', [['A1', 'BASE', 1, 70000]]);
        $this->venta('002', '2026-11-05', [['A1', 'BASE', 3, 50000]]);
        $this->venta('003', '2026-11-07', [['A1', 'BASE', 1, 100000]]);
        $this->venta('004', '2026-11-08', [['A1', 'BASE', 1, 40000]]);
        $this->venta('', '2026-11-09', [['A1', 'BASE', 1, 25000]]);
        $this->venta('001', '2026-10-20', [['A1', 'BASE', 9, 100000]]);                         // otro mes
        $this->venta('002', '2026-11-11', [['A1', 'BASE', 9, 100000]], flag: '2');              // no completada
    }

    // ───────────── Servicio ─────────────

    public function test_avance_de_cada_vendedor_con_meta_ordenado_por_porcentaje(): void
    {
        $this->cargarMes();
        $this->meta('001', 500000);     // 270.000 → 54%
        $this->meta('002', 100000);     // 150.000 → 150%
        $this->meta('003', 1000000);    // 100.000 → 10%

        $d = $this->servicio->delMes(Carbon::parse('2026-11-01'));

        $this->assertSame(['002', '001', '003'], array_column($d['vendedores'], 'pernr'), 'de mayor a menor avance');
        [$bruno, $ana, $carla] = $d['vendedores'];
        $this->assertSame('Bruno', $bruno['nombre']);
        $this->assertSame([150.0, 'cumplida', 0.0], [$bruno['pct'], $bruno['estado'], $bruno['faltante']]);
        $this->assertSame([270000.0, 54.0, 'al_dia', 230000.0], [$ana['vendido'], $ana['pct'], $ana['estado'], $ana['faltante']]);
        $this->assertSame([10.0, 'atrasada'], [$carla['pct'], $carla['estado']]);
    }

    public function test_el_ritmo_esperado_depende_del_dia_del_mes(): void
    {
        $d = $this->servicio->delMes(Carbon::parse('2026-11-01'));
        $this->assertSame(53.3, $d['esperado'], '16 de 30 días');
        $this->assertFalse($d['cerrado']);
        $this->assertSame('Noviembre 2026', $d['mes']);

        $cerrado = $this->servicio->delMes(Carbon::parse('2026-10-01'));
        $this->assertSame(100.0, $cerrado['esperado']);
        $this->assertTrue($cerrado['cerrado']);
    }

    public function test_un_vendedor_justo_en_el_ritmo_esta_al_dia_y_por_debajo_atrasado(): void
    {
        $this->venta('001', '2026-11-02', [['A1', 'BASE', 1, 53300]]);   // 53,3% de 100.000
        $this->venta('002', '2026-11-02', [['A1', 'BASE', 1, 53200]]);   // 53,2%
        $this->meta('001', 100000);
        $this->meta('002', 100000);

        $estados = array_column($this->servicio->delMes(Carbon::parse('2026-11-01'))['vendedores'], 'estado', 'pernr');

        $this->assertSame(['001' => 'al_dia', '002' => 'atrasada'], $estados);
    }

    public function test_la_meta_de_la_empresa_cuenta_todas_las_ventas_incluso_sin_vendedor(): void
    {
        $this->cargarMes();
        $this->meta('', 1000000);

        $e = $this->servicio->delMes(Carbon::parse('2026-11-01'))['empresa'];

        $this->assertSame(1000000.0, $e['meta']);
        $this->assertSame(585000.0, $e['vendido'], '270.000 + 150.000 + 100.000 + 40.000 + 25.000');
        $this->assertSame(58.5, $e['pct']);
        $this->assertSame(415000.0, $e['faltante']);
    }

    public function test_sin_meta_de_empresa_no_hay_bloque_de_empresa_y_la_meta_de_vendedor_no_la_reemplaza(): void
    {
        $this->cargarMes();
        $this->meta('001', 500000);

        $this->assertNull($this->servicio->delMes(Carbon::parse('2026-11-01'))['empresa']);
    }

    public function test_los_vendedores_que_vendieron_sin_meta_se_avisan(): void
    {
        $this->cargarMes();
        $this->meta('001', 500000);

        $d = $this->servicio->delMes(Carbon::parse('2026-11-01'));

        $this->assertSame(['Bruno', 'Carla', 'Diego'], $d['sin_meta'], 'sin la venta sin vendedor');
    }

    public function test_el_resumen_cuenta_metas_cumplidas_y_promedio(): void
    {
        $this->cargarMes();
        $this->meta('001', 500000);    // 54
        $this->meta('002', 100000);    // 150
        $this->meta('003', 1000000);   // 10

        $r = $this->servicio->delMes(Carbon::parse('2026-11-01'))['resumen'];

        $this->assertSame(3, $r['con_meta']);
        $this->assertSame(1, $r['cumplidas']);
        $this->assertSame(71.3, $r['promedio'], '(54 + 150 + 10) / 3');
    }

    public function test_un_mes_cerrado_usa_todo_el_mes_y_uno_futuro_no_tiene_ventas(): void
    {
        $this->cargarMes();
        $this->meta('001', 1000000, '2026-10-01');
        $this->meta('001', 500000, '2026-12-01');

        $oct = $this->servicio->delMes(Carbon::parse('2026-10-15'))['vendedores'][0];
        $this->assertSame([900000.0, 90.0], [$oct['vendido'], $oct['pct']]);

        $dic = $this->servicio->delMes(Carbon::parse('2026-12-01'))['vendedores'][0];
        $this->assertSame([0.0, 0.0, 'atrasada'], [$dic['vendido'], $dic['pct'], $dic['estado']]);
    }

    public function test_solo_cuentan_las_metas_de_la_empresa_y_el_mes(): void
    {
        $otra = Empresa::create(['nombre' => 'Otra', 'db_name' => 'otra']);
        $this->meta('001', 500000, '2026-11-01', $otra->id);
        $this->meta('002', 500000, '2026-10-01');

        $d = $this->servicio->delMes(Carbon::parse('2026-11-01'));

        $this->assertSame([], $d['vendedores']);
        $this->assertSame(0, $d['resumen']['con_meta']);
        $this->assertSame(0.0, $d['resumen']['promedio']);
    }

    public function test_una_meta_en_cero_se_ignora(): void
    {
        $this->meta('001', 0);

        $this->assertSame([], $this->servicio->delMes(Carbon::parse('2026-11-01'))['vendedores']);
    }

    // ───────────── Dashboard administrativo ─────────────

    public function test_el_administrador_ve_el_dashboard_administrativo_con_la_pestana_de_metas(): void
    {
        $this->actingAs($this->usuario);

        Livewire::test(Dashboard::class)->assertSuccessful()->assertSee('General')->assertSee('Metas por vendedor');
        $this->assertContains(FinancialStatsWidget::class, (new Dashboard)->getWidgets());
    }

    public function test_la_pestana_de_metas_muestra_sus_widgets(): void
    {
        $this->actingAs($this->usuario);

        $t = Livewire::test(Dashboard::class)->set('filters.vista', 'metas');

        $this->assertSame([ResumenMetasWidget::class, MetasVendedoresWidget::class], $t->instance()->getWidgets());
    }

    public function test_el_vendedor_sigue_viendo_su_dashboard_y_no_las_metas_de_todos(): void
    {
        $vendedor = User::create([
            'name' => 'Ana', 'email' => 'ana@test.com', 'password' => bcrypt('x'), 'empresa_id' => $this->empresa->id,
            'role' => 'vendedor', 'personal_id' => '001', 'accesos' => ['dashboard' => true],
        ]);
        $this->actingAs($vendedor);

        $this->assertFalse(ResumenMetasWidget::canView());
        $this->assertFalse(MetasVendedoresWidget::canView());
        $this->assertNotContains(ResumenMetasWidget::class, (new Dashboard)->getWidgets());
    }

    public function test_el_widget_lista_a_los_vendedores_con_su_porcentaje(): void
    {
        $this->cargarMes();
        $this->meta('001', 500000);
        $this->meta('002', 100000);
        $this->meta('003', 1000000);
        $this->actingAs($this->usuario);

        Livewire::test(MetasVendedoresWidget::class, ['filters' => ['vista' => 'metas', 'mes_metas' => '2026-11-01']])
            ->assertSee('Metas por vendedor')->assertSee('Noviembre 2026')
            ->assertSee('Bruno')->assertSee('150,0%')->assertSee('Cumplida')
            ->assertSee('Vendedora')->assertSee('54,0%')->assertSee('Al día')
            ->assertSee('Carla')->assertSee('10,0%')->assertSee('Atrasada')
            ->assertSee('Diego')->assertSee('no tienen meta');
    }

    public function test_el_widget_avisa_cuando_no_hay_metas_por_vendedor(): void
    {
        $this->actingAs($this->usuario);

        Livewire::test(MetasVendedoresWidget::class, ['filters' => ['vista' => 'metas']])
            ->assertSee('No hay metas por vendedor para Noviembre 2026');
    }

    public function test_en_un_mes_cerrado_dice_no_alcanzo(): void
    {
        $this->cargarMes();
        $this->meta('003', 1000000, '2026-10-01');
        $this->actingAs($this->usuario);

        Livewire::test(MetasVendedoresWidget::class, ['filters' => ['vista' => 'metas', 'mes_metas' => '2026-10-01']])
            ->assertSee('mes cerrado')->assertSee('No alcanzó')->assertDontSee('Atrasada');
    }

    public function test_el_widget_resumen_muestra_la_meta_de_la_empresa_y_los_cumplimientos(): void
    {
        $this->cargarMes();
        $this->meta('', 1000000);
        $this->meta('001', 500000);
        $this->meta('002', 100000);
        $this->actingAs($this->usuario);

        Livewire::test(ResumenMetasWidget::class, ['filters' => ['vista' => 'metas', 'mes_metas' => '2026-11-01']])
            ->assertSee('Meta de la empresa')->assertSee('58,5%')->assertSee('Gs. 1.000.000')
            ->assertSee('Vendedores con meta')->assertSee('Metas cumplidas')->assertSee('1 de 2')
            ->assertSee('Cumplimiento promedio')->assertSee('102,0%');
    }

    public function test_sin_meta_de_empresa_el_resumen_lo_avisa(): void
    {
        $this->actingAs($this->usuario);

        Livewire::test(ResumenMetasWidget::class, ['filters' => ['vista' => 'metas']])->assertSee('Sin meta')->assertSee('Cargala en Metas de Ventas');
    }

    public function test_un_mes_invalido_en_el_filtro_usa_el_actual(): void
    {
        $this->actingAs($this->usuario);

        Livewire::test(MetasVendedoresWidget::class, ['filters' => ['vista' => 'metas', 'mes_metas' => 'basura']])->assertSee('Noviembre 2026');
    }

    // ───────────── Pantalla de Metas de Ventas: dos opciones ─────────────

    public function test_se_crea_una_meta_de_la_empresa_o_una_por_vendedor(): void
    {
        $this->actingAs($this->usuario);

        Livewire::test(CreateMeta::class)
            ->assertSee('De la empresa')->assertSee('Por vendedor')
            ->fillForm(['tipo' => 'empresa', 'mes' => '2026-11-01', 'monto' => 5000000])
            ->call('create')->assertHasNoFormErrors();
        Livewire::test(CreateMeta::class)
            ->fillForm(['tipo' => 'vendedor', 'personal_id' => '002', 'mes' => '2026-11-01', 'monto' => 700000])
            ->call('create')->assertHasNoFormErrors();

        $this->assertSame('', Meta::where('monto', 5000000)->value('personal_id'));
        $this->assertSame('002', Meta::where('monto', 700000)->value('personal_id'));
    }

    public function test_una_meta_por_vendedor_exige_elegir_el_vendedor(): void
    {
        $this->actingAs($this->usuario);

        Livewire::test(CreateMeta::class)
            ->fillForm(['tipo' => 'vendedor', 'mes' => '2026-11-01', 'monto' => 700000])
            ->call('create')
            ->assertHasFormErrors(['personal_id']);

        $this->assertSame(0, Meta::count());
    }

    public function test_cambiar_a_meta_de_la_empresa_deja_el_vendedor_vacio_al_editar(): void
    {
        $this->actingAs($this->usuario);
        $this->meta('001', 500000);
        $id = Meta::first()->id;

        Livewire::test(EditMeta::class, ['record' => $id])
            ->assertFormSet(['tipo' => 'vendedor', 'personal_id' => '001'])
            ->fillForm(['tipo' => 'empresa'])
            ->call('save')->assertHasNoFormErrors();

        $this->assertSame('', Meta::find($id)->personal_id);
    }

    public function test_el_listado_se_separa_en_pestanas(): void
    {
        $this->actingAs($this->usuario);
        $this->meta('', 5000000);
        $this->meta('001', 500000);
        $this->meta('002', 100000);

        $this->assertSame(3, Livewire::test(ListMetas::class)->instance()->getTableRecords()->count());
        $this->assertSame(1, Livewire::test(ListMetas::class)->set('activeTab', 'empresa')->instance()->getTableRecords()->count());
        $this->assertSame(2, Livewire::test(ListMetas::class)->set('activeTab', 'vendedor')->instance()->getTableRecords()->count());
    }
}
