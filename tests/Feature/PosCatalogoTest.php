<?php

namespace Tests\Feature;

use App\Filament\Pages\PosPage;
use App\Models\ItemFicha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BaseDelphiSqlite;
use Tests\TestCase;

/** POS Catálogo / Boutique: grilla con fotos, "Cargar más", ficha del producto y venta. */
class PosCatalogoTest extends TestCase
{
    use BaseDelphiSqlite, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararDelphi();
        $this->prepararEmpresa(['tipo' => 'catalogo']);
    }

    private function cantidadEnGrilla(): int
    {
        return count(Livewire::test(PosPage::class)->get('searchResults'));
    }

    private function cargarProductos(int $n, float $stock = 10): void
    {
        for ($i = 1; $i <= $n; $i++) {
            $this->producto(sprintf('P%03d', $i), ['descr' => sprintf('PRODUCTO %03d', $i)], $stock);
        }
    }

    public function test_el_catalogo_arranca_con_la_grilla_cargada_y_dibuja_tarjetas(): void
    {
        $this->cargarProductos(3);

        Livewire::test(PosPage::class)
            ->assertSuccessful()
            ->assertSee('cat-grid', false)
            ->assertSee('PRODUCTO 001')
            ->assertSee('Agregar')
            ->assertSee('Información')
            ->assertSee('Maquillaje');
    }

    public function test_los_otros_tipos_no_cambian_arrancan_sin_grilla(): void
    {
        $this->cargarProductos(3);

        foreach (['general', 'supermercado'] as $tipo) {
            $this->cambiarTipo($tipo);
            $t = Livewire::test(PosPage::class);

            $this->assertSame([], $t->get('searchResults'), "tipo {$tipo}");
            $t->assertDontSee('cat-grid', false);
        }
    }

    public function test_los_otros_tipos_siguen_limitando_a_12_resultados(): void
    {
        $this->cargarProductos(20);
        $this->cambiarTipo('general');

        $t = Livewire::test(PosPage::class)->set('search', 'PRODUCTO');

        $this->assertCount(12, $t->get('searchResults'));
        $t->assertSet('hayMasProductos', false);
    }

    public function test_muestra_24_y_cargar_mas_suma_otros_24(): void
    {
        $this->cargarProductos(60);

        $t = Livewire::test(PosPage::class);
        $this->assertCount(24, $t->get('searchResults'));
        $t->assertSet('hayMasProductos', true);

        $t->call('cargarMas');
        $this->assertCount(48, $t->get('searchResults'));
        $t->assertSet('hayMasProductos', true);

        $t->call('cargarMas');
        $this->assertCount(60, $t->get('searchResults'));
        $t->assertSet('hayMasProductos', false);
    }

    public function test_cargar_mas_no_agrega_nada_al_carrito_aunque_la_busqueda_sea_un_codigo_exacto(): void
    {
        $this->cargarProductos(40);
        $this->producto('7790001112223', ['descr' => 'CODIGO LARGO', 'scode1' => '7790001112223']);

        $t = Livewire::test(PosPage::class)->set('search', 'PRODUCTO');
        $t->call('cargarMas');

        $t->assertSet('cart', []);
    }

    public function test_cambiar_de_categoria_o_buscar_vuelve_a_la_primera_tanda(): void
    {
        $this->cargarProductos(30);

        $t = Livewire::test(PosPage::class)->call('cargarMas');
        $this->assertCount(30, $t->get('searchResults'));

        $t->set('categoriaId', 'CP');
        $this->assertCount(0, $t->get('searchResults'));

        $t->set('categoriaId', null)->set('search', 'PRODUCTO');
        $this->assertCount(24, $t->get('searchResults'));
    }

    public function test_el_buscador_filtra_y_al_borrarlo_vuelve_la_grilla(): void
    {
        $this->cargarProductos(5);
        $this->producto('LAB1', ['descr' => 'LABIAL ROJO']);

        $t = Livewire::test(PosPage::class)->set('search', 'labial');
        $this->assertSame(['LAB1'], array_column(array_map(fn ($r) => (array) $r, $t->get('searchResults')), 'item'));

        $t->set('search', '');
        $this->assertCount(6, $t->get('searchResults'));
    }

    public function test_un_codigo_de_barras_exacto_se_agrega_solo_y_la_grilla_se_recarga(): void
    {
        $this->cargarProductos(3);
        $this->producto('B1', ['descr' => 'CON BARRAS', 'scode1' => '7791234567890']);

        $t = Livewire::test(PosPage::class)->set('search', '7791234567890');

        $this->assertCount(1, $t->get('cart'));
        $this->assertSame('B1', $t->get('cart')[0]['item']);
        $this->assertCount(4, $t->get('searchResults'), 'la grilla vuelve a mostrar el catálogo');
    }

    public function test_respeta_mostrar_productos_sin_stock(): void
    {
        $this->producto('CON', stock: 5);
        $this->producto('SIN', stock: 0);

        $this->empresa->update(['mostrar_productos_sin_stock' => true]);
        $this->usuario->unsetRelation('empresa');
        $this->assertSame(2, $this->cantidadEnGrilla());

        $this->empresa->update(['mostrar_productos_sin_stock' => false]);
        $this->usuario->unsetRelation('empresa');
        $this->assertSame(1, $this->cantidadEnGrilla());
    }

    public function test_la_tarjeta_marca_agotado_y_ultimas_unidades_y_bloquea_agregar_sin_stock(): void
    {
        $this->producto('HAY', ['descr' => 'TIENE STOCK'], stock: 10);
        $this->producto('POCAS', ['descr' => 'POCO STOCK'], stock: 2);
        $this->producto('NADA', ['descr' => 'SIN STOCK'], stock: 0);

        $html = Livewire::test(PosPage::class)->html();

        $this->assertStringContainsString('Últimas unidades', $html);
        $this->assertStringContainsString('cat-etq-agotado', $html);
        $this->assertSame(1, substr_count($html, 'cat-etq-agotado">'));
        $this->assertSame(1, substr_count($html, 'cat-etq-pocas">'));
        $this->assertMatchesRegularExpression('/cat-btn-add"[^>]*wire:click="addToCartKeepSearch\(\'NADA\'\)"\s+disabled/s', $html);
    }

    public function test_con_permitir_venta_sin_stock_el_boton_agregar_queda_habilitado(): void
    {
        $this->empresa->update(['permitir_venta_sin_stock' => true]);
        $this->usuario->unsetRelation('empresa');
        $this->producto('NADA', stock: 0);

        $html = Livewire::test(PosPage::class)->html();

        $this->assertDoesNotMatchRegularExpression('/addToCartKeepSearch\(\'NADA\'\)"\s+disabled/', $html);
    }

    public function test_la_tarjeta_muestra_la_marca_de_la_ficha_y_la_foto_solo_si_existe(): void
    {
        $this->producto('CON', ['descr' => 'CON FOTO']);
        $this->foto('CON');
        $this->producto('SIN', ['descr' => 'SIN FOTO']);
        ItemFicha::create(['item' => 'CON', 'marca' => 'Maybelline']);

        $t = Livewire::test(PosPage::class);
        $html = $t->html();

        $this->assertStringContainsString('Maybelline', $html);
        $this->assertSame(1, substr_count($html, '/api/item-image/CON'), 'solo se pide la foto del que la tiene');
        $this->assertStringNotContainsString('/api/item-image/SIN', $html);
    }

    // ───────────── Modal "Información del producto" ─────────────

    private function productoConFicha(string $codigo = 'P1'): void
    {
        $this->producto($codigo, ['descr' => 'BASE FLUIDA', 'descr1' => 'corta', 'precio3' => 80000, 'precio1' => 60000, 'costo' => 33333.3], stock: 4);
        $this->foto($codigo);
        $this->foto($codigo);
        ItemFicha::create([
            'item' => $codigo, 'marca' => 'Mary Kay', 'contenido' => '30 ml', 'descripcion' => 'Cobertura alta', 'beneficios' => 'Hidrata 24h',
            'modo_uso' => 'Con esponja', 'tipo_piel' => 'Mixta', 'advertencias' => 'Solo uso externo', 'destacado' => true,
            'tonos' => [['nombre' => 'Nude', 'color' => '#d8a48f']],
        ]);
    }

    public function test_el_boton_informacion_abre_la_ficha_completa(): void
    {
        $this->productoConFicha();

        Livewire::test(PosPage::class)
            ->call('verInfoProducto', 'P1')
            ->assertSet('infoItem', 'P1')
            ->assertSee('role="dialog"', false)
            ->assertSee('BASE FLUIDA')
            ->assertSee('Mary Kay')
            ->assertSee('30 ml')
            ->assertSee('Cobertura alta')
            ->assertSee('Hidrata 24h')
            ->assertSee('Con esponja')
            ->assertSee('Mixta')
            ->assertSee('Solo uso externo')
            ->assertSee('Destacado')
            ->assertSee('Gs.&nbsp;80.000', false)
            ->assertSee('Precio mayorista: Gs.&nbsp;60.000', false)
            ->assertSee('Disponible')
            ->assertSee('Agregar a la venta')
            ->assertSee('background: #d8a48f', false);
    }

    public function test_la_ficha_oculta_las_secciones_vacias(): void
    {
        $this->producto('P1', ['descr' => 'SIMPLE', 'descr1' => 'Solo descripción corta']);

        $html = Livewire::test(PosPage::class)->call('verInfoProducto', 'P1')->html();

        $this->assertStringContainsString('Solo descripción corta', $html);
        $this->assertStringContainsString('>Descripción<', $html);
        foreach (['Beneficios', 'Modo de uso', 'Ingredientes', 'Tipo de piel', 'Tonos', 'Advertencias', 'Precio mayorista'] as $titulo) {
            $this->assertStringNotContainsString(">{$titulo}", $html, $titulo);
        }
    }

    public function test_la_ficha_funciona_aunque_la_base_no_tenga_items_ficha(): void
    {
        $this->prepararDelphi(conFicha: false);
        $this->producto('P1', ['descr' => 'SIN TABLA', 'descr1' => 'Descripción de respaldo']);

        Livewire::test(PosPage::class)
            ->assertSuccessful()
            ->call('verInfoProducto', 'P1')
            ->assertSee('SIN TABLA')
            ->assertSee('Descripción de respaldo');
    }

    public function test_la_ficha_y_el_modal_nunca_exponen_el_costo(): void
    {
        $this->productoConFicha();
        $this->producto('P2', ['descr' => 'RELACIONADO', 'costo' => 33333.3]);

        $t = Livewire::test(PosPage::class)->call('verInfoProducto', 'P1');

        $modal = $t->html();
        $modal = substr($modal, strpos($modal, 'class="info-back"'));
        $modal = substr($modal, 0, strpos($modal, 'MODAL') ?: null);

        $this->assertStringNotContainsStringIgnoringCase('costo', json_encode($t->get('infoFicha')));
        $this->assertStringNotContainsStringIgnoringCase('costo', json_encode($t->get('infoRelacionados')));
        $this->assertStringNotContainsString('33333', json_encode([$t->get('infoFicha'), $t->get('infoRelacionados')]));
        $this->assertStringNotContainsString('33333', $modal);
    }

    public function test_la_ficha_ofrece_relacionados_de_la_misma_categoria(): void
    {
        $this->productoConFicha();
        foreach (['A', 'B', 'C', 'D', 'E'] as $c) {
            $this->producto("R{$c}", ['descr' => "OTRO {$c}"]);
        }
        $this->producto('OTRA', ['descr' => 'DE OTRA CATEGORIA', 'grupnr' => 'CP']);

        $t = Livewire::test(PosPage::class)->call('verInfoProducto', 'P1');

        $this->assertCount(4, $t->get('infoRelacionados'));
        $t->assertSee('También te puede interesar');
        $this->assertNotContains('OTRA', array_column($t->get('infoRelacionados'), 'item'));

        // tocar un relacionado abre su ficha
        $t->call('verInfoProducto', 'RA')->assertSet('infoItem', 'RA')->assertSee('OTRO A');
    }

    public function test_los_relacionados_sin_stock_se_ocultan_si_la_empresa_no_muestra_productos_sin_stock(): void
    {
        $this->empresa->update(['mostrar_productos_sin_stock' => false]);
        $this->usuario->unsetRelation('empresa');
        $this->producto('P1');
        $this->producto('HAY', stock: 3);
        $this->producto('NADA', stock: 0);

        $t = Livewire::test(PosPage::class)->call('verInfoProducto', 'P1');

        $this->assertSame(['HAY'], array_column($t->get('infoRelacionados'), 'item'));
    }

    public function test_cerrar_la_ficha_limpia_el_estado(): void
    {
        $this->productoConFicha();

        Livewire::test(PosPage::class)
            ->call('verInfoProducto', 'P1')
            ->call('cerrarInfoProducto')
            ->assertSet('infoItem', null)
            ->assertSet('infoFicha', [])
            ->assertSet('infoRelacionados', [])
            ->assertDontSee('info-panel" x-ref', false);
    }

    public function test_un_producto_inexistente_o_un_codigo_invalido_no_abre_nada(): void
    {
        Livewire::test(PosPage::class)
            ->call('verInfoProducto', 'NO-EXISTE')->assertSet('infoItem', null)
            ->call('verInfoProducto', str_repeat('X', 40))->assertSet('infoItem', null)
            ->call('verInfoProducto', '')->assertSet('infoItem', null);
    }

    public function test_los_otros_tipos_de_pos_no_abren_la_ficha(): void
    {
        $this->productoConFicha();
        $this->cambiarTipo('general');

        Livewire::test(PosPage::class)->call('verInfoProducto', 'P1')->assertSet('infoItem', null)->assertSet('infoFicha', []);
    }

    // ───────────── Agregar a la venta desde la ficha ─────────────

    public function test_agregar_desde_la_ficha_suma_la_cantidad_y_cierra(): void
    {
        $this->productoConFicha();

        $t = Livewire::test(PosPage::class)->call('verInfoProducto', 'P1')->call('agregarDesdeFicha', 'P1', 3);

        $this->assertCount(1, $t->get('cart'));
        $this->assertSame(3, $t->get('cart')[0]['qty']);
        $this->assertEquals(80000, $t->get('cart')[0]['precio']);
        $t->assertSet('infoItem', null);
    }

    public function test_agregar_desde_la_ficha_suma_a_lo_que_ya_hay_sin_duplicar_la_linea(): void
    {
        $this->productoConFicha();

        $t = Livewire::test(PosPage::class)->call('agregarDesdeFicha', 'P1', 1)->call('agregarDesdeFicha', 'P1', 2);

        $this->assertCount(1, $t->get('cart'));
        $this->assertSame(3, $t->get('cart')[0]['qty']);
    }

    public function test_agregar_mas_que_el_stock_agrega_lo_que_hay_y_deja_la_ficha_abierta(): void
    {
        $this->productoConFicha(); // stock 4

        $t = Livewire::test(PosPage::class)->call('verInfoProducto', 'P1')->call('agregarDesdeFicha', 'P1', 9);

        $this->assertSame(4, $t->get('cart')[0]['qty']);
        $t->assertSet('infoItem', 'P1');
    }

    public function test_un_producto_sin_stock_no_se_agrega_desde_la_ficha(): void
    {
        $this->producto('NADA', stock: 0);

        $t = Livewire::test(PosPage::class)->call('verInfoProducto', 'NADA')->call('agregarDesdeFicha', 'NADA', 1);

        $t->assertSet('cart', [])->assertSet('infoItem', 'NADA');
    }

    public function test_con_permitir_venta_sin_stock_se_puede_agregar_desde_la_ficha(): void
    {
        $this->empresa->update(['permitir_venta_sin_stock' => true]);
        $this->usuario->unsetRelation('empresa');
        $this->producto('NADA', stock: 0);

        $t = Livewire::test(PosPage::class)->call('agregarDesdeFicha', 'NADA', 2);

        $this->assertSame(2, $t->get('cart')[0]['qty']);
    }

    public function test_agregar_un_relacionado_que_no_esta_en_la_grilla_no_cambia_la_grilla(): void
    {
        $this->cargarProductos(30);
        $this->producto('FUERA', ['descr' => 'ZZZ FUERA DE LA GRILLA']);

        $t = Livewire::test(PosPage::class);
        $antes = array_column(array_map(fn ($r) => (array) $r, $t->get('searchResults')), 'item');
        $this->assertNotContains('FUERA', $antes);

        $t->call('agregarDesdeFicha', 'FUERA', 1);

        $this->assertSame('FUERA', $t->get('cart')[0]['item']);
        $this->assertSame($antes, array_column(array_map(fn ($r) => (array) $r, $t->get('searchResults')), 'item'));
    }

    public function test_agregar_desde_la_ficha_ignora_cantidades_absurdas_y_codigos_invalidos(): void
    {
        $this->empresa->update(['permitir_venta_sin_stock' => true]);
        $this->usuario->unsetRelation('empresa');
        $this->producto('P1', stock: 0);

        $t = Livewire::test(PosPage::class)
            ->call('agregarDesdeFicha', 'P1', -5)
            ->call('agregarDesdeFicha', 'NO-EXISTE', 2)
            ->call('agregarDesdeFicha', str_repeat('X', 40), 1);

        $this->assertCount(1, $t->get('cart'));
        $this->assertSame(1, $t->get('cart')[0]['qty']);
    }

    public function test_los_otros_tipos_de_pos_no_agregan_desde_la_ficha(): void
    {
        $this->producto('P1');
        $this->cambiarTipo('general');

        Livewire::test(PosPage::class)->call('agregarDesdeFicha', 'P1', 2)->assertSet('cart', []);
    }

    public function test_el_carrito_del_catalogo_cobra_con_el_mismo_modal_de_cobro(): void
    {
        $this->producto('P1', ['precio3' => 50000]);

        Livewire::test(PosPage::class)
            ->call('agregarDesdeFicha', 'P1', 2)
            ->assertSet('showPaymentModal', false)
            ->call('openPaymentModal')
            ->assertSet('showPaymentModal', true)
            ->assertSee('100.000');
    }
}
