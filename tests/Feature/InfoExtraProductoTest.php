<?php

namespace Tests\Feature;

use App\Filament\Pages\PosPage;
use App\Filament\Resources\ItemResource\Pages\CreateItem;
use App\Filament\Resources\ItemResource\Pages\EditItem;
use App\Models\Empresa;
use App\Models\ProductoInfoExtra;
use App\Models\User;
use App\Services\Productos\FichaProducto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Concerns\BaseDelphiSqlite;
use Tests\TestCase;

/** Secciones de información que cada empresa agrega a la ficha con el título que quiera (mismo formato que los tonos). */
class InfoExtraProductoTest extends TestCase
{
    use BaseDelphiSqlite, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararDelphi();
        $this->prepararEmpresa(['tipo' => 'catalogo']);
        $this->producto('P1', ['descr' => 'BASE FLUIDA']);
    }

    private function filaExtra(string $item = 'P1', ?int $empresa = null): ?ProductoInfoExtra
    {
        return ProductoInfoExtra::where('empresa_id', $empresa ?? $this->empresa->id)->where('item', $item)->first();
    }

    // ───────────── Modelo ─────────────

    public function test_limpiar_descarta_secciones_incompletas_y_recorta(): void
    {
        $r = ProductoInfoExtra::limpiar([
            ['titulo' => '  Garantía ', 'contenido' => ' 6 meses '],
            ['titulo' => '', 'contenido' => 'sin título'],
            ['titulo' => 'Sin contenido', 'contenido' => '  '],
            'basura',
            ['titulo' => str_repeat('T', 100), 'contenido' => str_repeat('C', 3000)],
        ]);

        $this->assertCount(2, $r);
        $this->assertSame(['titulo' => 'Garantía', 'contenido' => '6 meses'], $r[0]);
        $this->assertSame(ProductoInfoExtra::LARGO_TITULO, mb_strlen($r[1]['titulo']));
        $this->assertSame(ProductoInfoExtra::LARGO_CONTENIDO, mb_strlen($r[1]['contenido']));
    }

    public function test_limpiar_respeta_el_maximo_y_tolera_valores_raros(): void
    {
        $muchas = array_map(fn ($i) => ['titulo' => "T{$i}", 'contenido' => "C{$i}"], range(1, 30));

        $this->assertCount(ProductoInfoExtra::MAXIMO, ProductoInfoExtra::limpiar($muchas));
        $this->assertSame([], ProductoInfoExtra::limpiar(null));
        $this->assertSame([], ProductoInfoExtra::limpiar('texto'));
    }

    public function test_al_crear_toma_la_empresa_del_usuario(): void
    {
        $m = ProductoInfoExtra::create(['item' => 'P1', 'secciones' => [['titulo' => 'A', 'contenido' => 'B']]]);

        $this->assertSame($this->empresa->id, $m->empresa_id);
    }

    // ───────────── Formulario de Productos ─────────────

    public function test_la_pestana_de_informacion_trae_el_formato_para_agregar_secciones(): void
    {
        Livewire::test(EditItem::class, ['record' => 'P1'])
            ->assertSee('Información del producto')
            ->assertSee('Más información')
            ->assertSee('Agregar información');
    }

    public function test_agregar_secciones_desde_el_formulario_y_editarlas_sin_duplicar(): void
    {
        Livewire::test(EditItem::class, ['record' => 'P1'])
            ->fillForm(['infoExtra.secciones' => [
                ['titulo' => 'Garantía', 'contenido' => '6 meses por defectos de fábrica'],
                ['titulo' => 'Cuidados', 'contenido' => 'No exponer al sol'],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(
            [['titulo' => 'Garantía', 'contenido' => '6 meses por defectos de fábrica'], ['titulo' => 'Cuidados', 'contenido' => 'No exponer al sol']],
            $this->filaExtra()->secciones
        );

        $t = Livewire::test(EditItem::class, ['record' => 'P1']);
        $this->assertCount(2, $t->get('data.infoExtra.secciones'));
        $t->fillForm(['infoExtra.secciones' => [['titulo' => 'Origen', 'contenido' => 'Paraguay']]])->call('save');

        $this->assertSame(1, ProductoInfoExtra::where('item', 'P1')->count());
        $this->assertSame([['titulo' => 'Origen', 'contenido' => 'Paraguay']], $this->filaExtra()->secciones);
    }

    public function test_guardar_sin_agregar_nada_no_crea_filas(): void
    {
        Livewire::test(EditItem::class, ['record' => 'P1'])->call('save')->assertHasNoFormErrors();

        $this->assertSame(0, ProductoInfoExtra::count());
    }

    public function test_cada_seccion_exige_titulo_y_contenido(): void
    {
        Livewire::test(EditItem::class, ['record' => 'P1'])
            ->fillForm(['infoExtra.secciones' => [['titulo' => '', 'contenido' => '']]])
            ->call('save')
            ->assertHasFormErrors();

        $this->assertSame(0, ProductoInfoExtra::count());
    }

    public function test_se_pueden_cargar_secciones_al_crear_un_producto(): void
    {
        Livewire::test(CreateItem::class)
            ->fillForm(['descr' => 'CREMA NUEVA', 'precio3' => 9000, 'infoExtra.secciones' => [['titulo' => 'Garantía', 'contenido' => '1 año']]])
            ->call('create')
            ->assertHasNoFormErrors();

        $item = DB::connection('delphi')->table('items')->where('descr', 'CREMA NUEVA')->value('item');
        $this->assertSame([['titulo' => 'Garantía', 'contenido' => '1 año']], $this->filaExtra($item)->secciones);
    }

    public function test_funciona_aunque_la_base_no_tenga_items_ficha(): void
    {
        $this->prepararDelphi(conFicha: false);
        $this->producto('P1');

        Livewire::test(EditItem::class, ['record' => 'P1'])
            ->assertSuccessful()
            ->assertSee('Más información')
            ->assertDontSee('Ficha del producto')
            ->fillForm(['infoExtra.secciones' => [['titulo' => 'Garantía', 'contenido' => '6 meses']]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Garantía', $this->filaExtra()->secciones[0]['titulo']);
    }

    // ───────────── Separación por empresa ─────────────

    public function test_cada_empresa_ve_solo_sus_propias_secciones(): void
    {
        ProductoInfoExtra::create(['item' => 'P1', 'secciones' => [['titulo' => 'Mía', 'contenido' => 'de mi empresa']]]);
        $otra = Empresa::create(['nombre' => 'Otra', 'db_name' => 'otra']);
        ProductoInfoExtra::create(['empresa_id' => $otra->id, 'item' => 'P1', 'secciones' => [['titulo' => 'Ajena', 'contenido' => 'de otra empresa']]]);

        $this->assertSame([['titulo' => 'Mía', 'contenido' => 'de mi empresa']], ProductoInfoExtra::de('P1'));

        $this->actingAs(User::create([
            'name' => 'Otro', 'email' => 'otro@test.com', 'password' => bcrypt('x'), 'empresa_id' => $otra->id,
            'role' => 'administrador', 'accesos' => ['productos' => true],
        ]));
        $this->assertSame([['titulo' => 'Ajena', 'contenido' => 'de otra empresa']], ProductoInfoExtra::de('P1'));
    }

    public function test_la_misma_empresa_no_puede_tener_dos_filas_para_un_producto(): void
    {
        ProductoInfoExtra::create(['item' => 'P1', 'secciones' => []]);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        ProductoInfoExtra::create(['item' => 'P1', 'secciones' => []]);
    }

    // ───────────── Ficha y POS ─────────────

    public function test_la_ficha_incluye_las_secciones_extra(): void
    {
        ProductoInfoExtra::create(['item' => 'P1', 'secciones' => [['titulo' => 'Garantía', 'contenido' => '6 meses']]]);

        $f = (new FichaProducto)->obtener('P1', 'D01');

        $this->assertSame([['titulo' => 'Garantía', 'contenido' => '6 meses']], $f['extras']);
        $this->assertSame([], (new FichaProducto)->obtener('P1')['tonos']);
    }

    public function test_un_producto_sin_secciones_extra_devuelve_lista_vacia(): void
    {
        $this->assertSame([], (new FichaProducto)->obtener('P1')['extras']);
    }

    public function test_el_modal_del_pos_muestra_las_secciones_con_su_titulo(): void
    {
        ProductoInfoExtra::create(['item' => 'P1', 'secciones' => [
            ['titulo' => 'Garantía', 'contenido' => '6 meses por defectos'],
            ['titulo' => 'Cuidados', 'contenido' => 'No exponer al sol'],
        ]]);

        $html = Livewire::test(PosPage::class)->call('verInfoProducto', 'P1')->html();

        foreach (['>Garantía<', '6 meses por defectos', '>Cuidados<', 'No exponer al sol'] as $texto) {
            $this->assertStringContainsString($texto, $html, $texto);
        }
    }

    public function test_el_modal_escapa_el_contenido_de_las_secciones(): void
    {
        ProductoInfoExtra::create(['item' => 'P1', 'secciones' => [['titulo' => '<b>x</b>', 'contenido' => '<script>alert(1)</script>']]]);

        $html = Livewire::test(PosPage::class)->call('verInfoProducto', 'P1')->html();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function test_las_advertencias_siguen_apareciendo_despues_de_las_secciones_extra(): void
    {
        DB::connection('delphi')->table('items_ficha')->insert(['item' => 'P1', 'advertencias' => 'Solo uso externo']);
        ProductoInfoExtra::create(['item' => 'P1', 'secciones' => [['titulo' => 'Garantía', 'contenido' => '6 meses']]]);

        $html = Livewire::test(PosPage::class)->call('verInfoProducto', 'P1')->html();

        $this->assertLessThan(strpos($html, 'Solo uso externo'), strpos($html, '>Garantía<'));
    }
}
