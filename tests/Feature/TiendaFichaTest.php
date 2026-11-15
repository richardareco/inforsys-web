<?php

namespace Tests\Feature;

use App\Models\EcomConfig;
use App\Models\ProductoInfoExtra;
use App\Services\Tienda\CatalogoTienda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BaseDelphiSqlite;
use Tests\TestCase;

/** La tienda online pública muestra la ficha del producto (marca, beneficios, tonos, secciones extra...). */
class TiendaFichaTest extends TestCase
{
    use BaseDelphiSqlite, RefreshDatabase;

    private EcomConfig $tienda;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararDelphi();
        // la tienda abre la base Delphi de la empresa por su nombre: en los tests es la SQLite en memoria
        $this->prepararEmpresa(['db_name' => config('database.connections.delphi.database')]);
        $this->tienda = EcomConfig::create(['empresa_id' => $this->empresa->id, 'activo' => true, 'slug' => 'boutique', 'nombre' => 'Boutique']);
        $this->producto('P1', ['descr' => 'BASE FLUIDA', 'descr1' => 'Descripción corta']);
        auth()->logout(); // la tienda es pública: sin usuario logueado
    }

    private function pagina(string $codigo = 'P1')
    {
        return $this->get('/tienda/boutique/producto/' . $codigo);
    }

    private function ficha(array $datos): void
    {
        DB::connection('delphi')->table('items_ficha')->insert(array_merge(['item' => 'P1'], $datos));
    }

    public function test_la_pagina_del_producto_muestra_la_ficha_completa(): void
    {
        $this->ficha([
            'marca' => 'Mary Kay', 'contenido' => '30 ml', 'descripcion' => 'Cobertura alta', 'beneficios' => 'Hidrata 24h',
            'modo_uso' => 'Con esponja', 'ingredientes' => 'Aqua, Glycerin', 'tipo_piel' => 'Mixta', 'advertencias' => 'Solo uso externo',
            'tonos' => json_encode([['nombre' => 'Nude', 'color' => '#d8a48f']]),
        ]);
        ProductoInfoExtra::create(['empresa_id' => $this->empresa->id, 'item' => 'P1', 'secciones' => [['titulo' => 'Garantía', 'contenido' => '6 meses']]]);

        $this->pagina()->assertOk()
            ->assertSee('Mary Kay')->assertSee('30 ml')->assertSee('Cobertura alta')
            ->assertSee('Beneficios')->assertSee('Hidrata 24h')
            ->assertSee('Modo de uso')->assertSee('Con esponja')
            ->assertSee('Ingredientes')->assertSee('Aqua, Glycerin')
            ->assertSee('Tipo de piel')->assertSee('Mixta')
            ->assertSee('Tonos')->assertSee('Nude')->assertSee('background: #d8a48f', false)
            ->assertSee('Garantía')->assertSee('6 meses')
            ->assertSee('Advertencias')->assertSee('Solo uso externo');
    }

    public function test_la_descripcion_de_la_ficha_reemplaza_a_la_corta(): void
    {
        $this->ficha(['descripcion' => 'Descripción de la ficha']);

        $this->pagina()->assertSee('<p class="desc">Descripción de la ficha</p>', false)->assertDontSee('<p class="desc">Descripción corta</p>', false);
    }

    public function test_sin_ficha_la_pagina_queda_como_antes(): void
    {
        $this->pagina()->assertOk()->assertSee('Descripción corta')->assertDontSee('<section class="ficha-sec', false)->assertDontSee('Beneficios');
    }

    public function test_solo_se_muestra_lo_que_esta_cargado(): void
    {
        $this->ficha(['beneficios' => 'Hidrata']);

        $this->pagina()->assertSee('Beneficios')->assertDontSee('Modo de uso')->assertDontSee('Ingredientes')->assertDontSee('Advertencias')->assertDontSee('Tonos');
    }

    public function test_funciona_aunque_la_base_no_tenga_la_tabla_items_ficha(): void
    {
        $this->prepararDelphi(conFicha: false);
        $this->tienda->empresa->update(['db_name' => config('database.connections.delphi.database')]);
        $this->producto('P1', ['descr' => 'BASE FLUIDA', 'descr1' => 'Descripción corta']);
        ProductoInfoExtra::create(['empresa_id' => $this->empresa->id, 'item' => 'P1', 'secciones' => [['titulo' => 'Garantía', 'contenido' => '6 meses']]]);

        $this->pagina()->assertOk()->assertSee('Descripción corta')->assertSee('Garantía')->assertSee('6 meses');
    }

    public function test_el_listado_de_la_tienda_muestra_la_marca_en_cada_tarjeta(): void
    {
        $this->ficha(['marca' => 'Mary Kay']);
        $this->producto('P2', ['descr' => 'SIN MARCA']);

        $this->get('/tienda/boutique')->assertOk()->assertSee('Mary Kay · ', false)->assertSee('SIN MARCA');
    }

    public function test_el_listado_no_muestra_la_marca_si_se_apaga_la_ficha(): void
    {
        $this->ficha(['marca' => 'Mary Kay']);
        $this->tienda->update(['mostrar_ficha' => false]);

        $this->get('/tienda/boutique')->assertOk()->assertDontSee('Mary Kay');
    }

    public function test_el_listado_funciona_sin_la_tabla_items_ficha(): void
    {
        $this->prepararDelphi(conFicha: false);
        $this->tienda->empresa->update(['db_name' => config('database.connections.delphi.database')]);
        $this->producto('P1', ['descr' => 'BASE FLUIDA']);

        $this->get('/tienda/boutique')->assertOk()->assertSee('BASE FLUIDA');
    }

    public function test_se_puede_apagar_desde_la_configuracion_de_la_tienda(): void
    {
        $this->ficha(['beneficios' => 'Hidrata 24h', 'marca' => 'Mary Kay']);
        $this->tienda->update(['mostrar_ficha' => false]);

        $this->pagina()->assertOk()->assertSee('Descripción corta')->assertDontSee('Hidrata 24h')->assertDontSee('Mary Kay');
    }

    public function test_viene_activado_por_defecto(): void
    {
        $this->assertTrue($this->tienda->fresh()->mostrar_ficha);
    }

    public function test_nunca_expone_costos_ni_stock(): void
    {
        $this->ficha(['marca' => 'X']);

        $datos = (new CatalogoTienda($this->tienda->fresh()->load('empresa')))->ficha('P1');

        $this->assertEqualsCanonicalizing(['marca', 'contenido', 'descripcion', 'beneficios', 'modo_uso', 'ingredientes', 'tipo_piel', 'tonos', 'extras', 'advertencias'], array_keys($datos));
        $this->assertStringNotContainsStringIgnoringCase('costo', json_encode($datos));
        $this->pagina()->assertDontSee('4321');
    }

    public function test_el_contenido_cargado_se_escapa_y_los_colores_invalidos_se_descartan(): void
    {
        $this->ficha([
            'beneficios' => '<script>alert(1)</script>',
            'tonos' => json_encode([['nombre' => 'Malo', 'color' => 'red;background:url(//x)'], ['nombre' => 'Bueno', 'color' => '#abc']]),
        ]);
        ProductoInfoExtra::create(['empresa_id' => $this->empresa->id, 'item' => 'P1', 'secciones' => [['titulo' => '<i>t</i>', 'contenido' => '<img src=x onerror=alert(1)>']]]);

        $r = $this->pagina()->assertOk();
        $r->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('<img src=x', false)->assertSee('&lt;script&gt;', false);
        $r->assertDontSee('url(//x)', false)->assertSee('background: #abc', false);
    }

    public function test_las_secciones_extra_de_otra_empresa_no_aparecen(): void
    {
        $otra = \App\Models\Empresa::create(['nombre' => 'Otra', 'db_name' => 'otra']);
        ProductoInfoExtra::create(['empresa_id' => $otra->id, 'item' => 'P1', 'secciones' => [['titulo' => 'Ajena', 'contenido' => 'no es mía']]]);

        $this->pagina()->assertOk()->assertDontSee('no es mía');
    }

    public function test_un_fallo_al_leer_la_ficha_no_tumba_la_pagina(): void
    {
        DB::connection('delphi')->statement('DROP TABLE items_ficha');
        DB::connection('delphi')->statement('CREATE TABLE items_ficha (nro integer, item varchar(20))'); // existe pero con otro formato

        $this->pagina()->assertOk()->assertSee('BASE FLUIDA');
    }

    public function test_la_configuracion_de_la_tienda_tiene_el_interruptor(): void
    {
        $this->actingAs($this->usuario);
        $this->usuario->update(['accesos' => ['ecommerce' => true]]);

        \Livewire\Livewire::test(\App\Filament\Pages\TiendaOnlinePage::class)->assertSee('Mostrar la información del producto');
    }
}
