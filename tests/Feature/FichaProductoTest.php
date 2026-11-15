<?php

namespace Tests\Feature;

use App\Models\ItemFicha;
use App\Services\Productos\FichaProducto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BaseDelphiSqlite;
use Tests\TestCase;

class FichaProductoTest extends TestCase
{
    use BaseDelphiSqlite, RefreshDatabase;

    private FichaProducto $fichas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararDelphi();
        $this->prepararEmpresa();
        $this->fichas = new FichaProducto;
    }

    public function test_sin_ficha_usa_la_descripcion_corta_del_producto(): void
    {
        $this->producto('P1', ['descr' => 'LABIAL MATE', 'descr1' => 'Labial de larga duración']);

        $f = $this->fichas->obtener('P1', 'D01');

        $this->assertSame('LABIAL MATE', $f['nombre']);
        $this->assertSame('Labial de larga duración', $f['descripcion']);
        $this->assertNull($f['beneficios']);
        $this->assertSame([], $f['tonos']);
        $this->assertFalse($f['destacado']);
    }

    public function test_sin_ficha_ni_descr1_la_descripcion_queda_vacia(): void
    {
        $this->producto('P1', ['descr1' => '']);

        $this->assertNull($this->fichas->obtener('P1')['descripcion']);
    }

    public function test_con_ficha_muestra_los_datos_comerciales(): void
    {
        $this->producto('P1', ['descr1' => 'corta']);
        ItemFicha::create([
            'item' => 'P1', 'marca' => 'Mary Kay', 'contenido' => '30 ml', 'descripcion' => 'Base fluida', 'beneficios' => 'Hidrata',
            'modo_uso' => 'Con esponja', 'ingredientes' => 'Aqua', 'tipo_piel' => 'Mixta', 'advertencias' => 'Uso externo', 'destacado' => true,
            'tonos' => [['nombre' => 'Nude', 'color' => '#d8a48f']],
        ]);

        $f = $this->fichas->obtener('P1', 'D01');

        $this->assertSame('Mary Kay', $f['marca']);
        $this->assertSame('30 ml', $f['contenido']);
        $this->assertSame('Base fluida', $f['descripcion'], 'la ficha manda sobre descr1');
        $this->assertSame('Hidrata', $f['beneficios']);
        $this->assertSame('Con esponja', $f['modo_uso']);
        $this->assertSame('Aqua', $f['ingredientes']);
        $this->assertSame('Mixta', $f['tipo_piel']);
        $this->assertSame('Uso externo', $f['advertencias']);
        $this->assertTrue($f['destacado']);
        $this->assertSame([['nombre' => 'Nude', 'color' => '#d8a48f']], $f['tonos']);
    }

    public function test_los_textos_vacios_de_la_ficha_cuentan_como_sin_dato(): void
    {
        $this->producto('P1', ['descr1' => 'corta']);
        DB::connection('delphi')->table('items_ficha')->insert(['item' => 'P1', 'descripcion' => '   ', 'beneficios' => '']);

        $f = $this->fichas->obtener('P1');

        $this->assertSame('corta', $f['descripcion']);
        $this->assertNull($f['beneficios']);
    }

    public function test_la_marca_sin_ficha_sale_de_la_tabla_marca_de_delphi(): void
    {
        DB::connection('delphi')->table('marca')->insert(['marnr' => '7', 'descr' => 'MAYBELLINE']);
        $this->producto('P1', ['marnr' => '7']);

        $this->assertSame('MAYBELLINE', $this->fichas->obtener('P1')['marca']);

        ItemFicha::create(['item' => 'P1', 'marca' => 'Maybelline NY']);
        $this->assertSame('Maybelline NY', $this->fichas->obtener('P1')['marca'], 'la marca de la ficha manda');
    }

    public function test_los_tonos_invalidos_se_descartan(): void
    {
        $this->producto('P1');
        DB::connection('delphi')->table('items_ficha')->insert(['item' => 'P1', 'tonos' => json_encode([
            ['nombre' => 'Bueno', 'color' => '#aabbcc'],
            ['nombre' => 'Inyección', 'color' => 'red;background:url(x)'],
            ['nombre' => '', 'color' => '#fff'],
            ['nombre' => 'Sin color'],
            'basura',
        ])]);

        $this->assertSame([['nombre' => 'Bueno', 'color' => '#aabbcc']], $this->fichas->obtener('P1')['tonos']);
    }

    public function test_un_json_de_tonos_roto_no_rompe_la_ficha(): void
    {
        $this->producto('P1');
        DB::connection('delphi')->table('items_ficha')->insert(['item' => 'P1', 'tonos' => '{no es json']);

        $this->assertSame([], $this->fichas->obtener('P1')['tonos']);
    }

    public function test_incluye_stock_del_deposito_categoria_precio_y_fotos_en_orden(): void
    {
        $this->producto('P1', ['grupnr' => 'MK', 'precio3' => 25000, 'precio1' => 20000], stock: 7);
        $a = $this->foto('P1');
        $b = $this->foto('P1');
        $this->foto('OTRO');

        $f = $this->fichas->obtener('P1', 'D01');

        $this->assertSame(7.0, $f['stock']);
        $this->assertSame('Maquillaje', $f['categoria']);
        $this->assertSame(25000.0, $f['precio']);
        $this->assertSame(20000.0, $f['precio_mayorista']);
        $this->assertSame([$a, $b], $f['fotos']);
    }

    public function test_el_stock_es_el_del_deposito_pedido(): void
    {
        $this->producto('P1', stock: 5);
        DB::connection('delphi')->table('itemsdepo')->insert(['depo' => 'D02', 'item' => 'P1', 'qty' => 3]);

        $this->assertSame(5.0, $this->fichas->obtener('P1', 'D01')['stock']);
        $this->assertSame(3.0, $this->fichas->obtener('P1', 'D02')['stock']);
        $this->assertSame(8.0, $this->fichas->obtener('P1')['stock'], 'sin depósito suma todos');
    }

    public function test_el_precio_mayorista_solo_aparece_si_es_menor_al_normal(): void
    {
        $this->producto('P1', ['precio3' => 10000, 'precio1' => 0]);
        $this->producto('P2', ['precio3' => 10000, 'precio1' => 12000]);
        $this->producto('P3', ['precio3' => 10000, 'precio1' => 8000]);

        $this->assertNull($this->fichas->obtener('P1')['precio_mayorista']);
        $this->assertNull($this->fichas->obtener('P2')['precio_mayorista']);
        $this->assertSame(8000.0, $this->fichas->obtener('P3')['precio_mayorista']);
    }

    public function test_un_producto_inexistente_devuelve_null(): void
    {
        $this->assertNull($this->fichas->obtener('NO-EXISTE'));
    }

    public function test_nunca_expone_el_costo(): void
    {
        $this->producto('P1', ['costo' => 4321.5]);
        ItemFicha::create(['item' => 'P1', 'marca' => 'X']);
        $this->producto('P2', ['costo' => 4321.5]);

        $f   = $this->fichas->obtener('P1', 'D01');
        $rel = $this->fichas->relacionados('P1', 'MK', 'D01');

        foreach ([$f, $rel] as $dato) {
            $json = json_encode($dato);
            $this->assertStringNotContainsStringIgnoringCase('costo', $json);
            $this->assertStringNotContainsString('4321', $json);
        }
        $this->assertSame(
            ['item', 'nombre', 'codigo_barras', 'categoria', 'grupnr', 'marca', 'contenido', 'descripcion', 'beneficios', 'modo_uso', 'ingredientes',
             'tipo_piel', 'advertencias', 'tonos', 'extras', 'destacado', 'precio', 'precio_mayorista', 'stock', 'fotos'],
            array_keys($f),
            'la ficha solo trae campos permitidos'
        );
    }

    public function test_relacionados_son_de_la_misma_categoria_maximo_4_y_sin_el_propio(): void
    {
        $this->producto('P1', ['grupnr' => 'MK']);
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $c) {
            $this->producto("R{$c}", ['grupnr' => 'MK', 'descr' => "REL {$c}"]);
        }
        $this->producto('OTRA', ['grupnr' => 'CP']);

        $rel = $this->fichas->relacionados('P1', 'MK', 'D01', 4);

        $this->assertCount(4, $rel);
        $codigos = array_column($rel, 'item');
        $this->assertNotContains('P1', $codigos);
        $this->assertNotContains('OTRA', $codigos);
        $this->assertSame([], $this->fichas->relacionados('P1', null));
    }

    public function test_relacionados_prefiere_los_que_tienen_stock(): void
    {
        $this->producto('P1', ['grupnr' => 'MK']);
        $this->producto('AGOTADO', ['grupnr' => 'MK', 'descr' => 'AAA'], stock: 0);
        $this->producto('HAY', ['grupnr' => 'MK', 'descr' => 'ZZZ'], stock: 4);

        $rel = $this->fichas->relacionados('P1', 'MK', 'D01', 1);

        $this->assertSame('HAY', $rel[0]['item']);
    }

    public function test_sin_la_tabla_items_ficha_no_rompe_y_cae_en_descr1(): void
    {
        $this->prepararDelphi(conFicha: false);
        $this->producto('P1', ['descr1' => 'Descripción corta']);
        $this->producto('P2', ['grupnr' => 'MK']);

        $this->assertFalse(FichaProducto::tablaExiste());

        $f = $this->fichas->obtener('P1', 'D01');
        $this->assertSame('Descripción corta', $f['descripcion']);
        $this->assertNull($f['marca']);
        $this->assertNull($f['beneficios']);
        $this->assertSame([], $f['tonos']);
        $this->assertNotEmpty($this->fichas->relacionados('P1', 'MK', 'D01'));
    }

    public function test_tabla_existe_detecta_cada_base_por_separado(): void
    {
        $this->assertTrue(FichaProducto::tablaExiste());

        $this->prepararDelphi(conFicha: false);
        $this->assertFalse(FichaProducto::tablaExiste());
    }
}
