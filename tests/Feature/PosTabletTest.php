<?php

namespace Tests\Feature;

use App\Filament\Pages\PosPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BaseDelphiSqlite;
use Tests\TestCase;

/**
 * En tablets el POS devolvía el foco al buscador tras cada producto agregado y el teclado en pantalla se abría y se cerraba:
 * la pantalla parpadeaba y aparecían los controles del sistema (salir de pantalla completa...). El buscador ahora recibe el
 * foco (para los lectores de código) pero sin teclado en pantalla, que aparece solo si la persona toca el campo.
 *
 * El comportamiento depende del navegador táctil, que no se puede ejecutar acá: estos tests protegen que el código que lo logra siga en las tres pantallas.
 */
class PosTabletTest extends TestCase
{
    use BaseDelphiSqlite, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararDelphi();
        $this->prepararEmpresa();
    }

    private function html(string $tipo): string
    {
        $this->cambiarTipo($tipo);

        return Livewire::test(PosPage::class)->html();
    }

    public static function tipos(): array
    {
        return ['general' => ['general'], 'catálogo' => ['catalogo'], 'supermercado' => ['supermercado']];
    }

    /** @dataProvider tipos */
    public function test_el_buscador_no_abre_el_teclado_en_pantalla_en_dispositivos_tactiles(string $tipo): void
    {
        $html = $this->html($tipo);

        $this->assertStringContainsString("x-bind:inputmode=\"tactil &amp;&amp; !tecladoPedido ? 'none' : 'search'\"", str_replace('&&', '&amp;&amp;', $html));
        $this->assertStringContainsString('@pointerdown="pedirTeclado($event)"', $html);
        $this->assertStringContainsString("@blur=\"tecladoPedido = false\"", $html);
    }

    /** @dataProvider tipos */
    public function test_detecta_la_pantalla_tactil_con_el_puntero_principal(string $tipo): void
    {
        $this->assertStringContainsString("tactil: !!(window.matchMedia && window.matchMedia('(pointer: coarse)').matches)", $this->html($tipo));
    }

    /** @dataProvider tipos */
    public function test_tocar_el_campo_pide_el_teclado_y_el_blur_lo_apaga(string $tipo): void
    {
        $html = $this->html($tipo);

        $this->assertStringContainsString('pedirTeclado(e) {', $html);
        $this->assertStringContainsString('if (!this.tactil || this.tecladoPedido) return;', $html);
        $this->assertStringContainsString('this.$nextTick(() => { this.tecladoPedido = true; this.$nextTick(() => el.focus()); });', $html);
    }

    /** @dataProvider tipos */
    public function test_el_foco_automatico_pasa_por_un_solo_metodo(string $tipo): void
    {
        if ($tipo === 'supermercado') {
            $this->markTestSkipped('El POS de supermercado enfoca el buscador con su propio focusSearch()');
        }

        $html = $this->html($tipo);

        $this->assertStringContainsString('@focus-search.window="if (window.innerWidth > 767) enfocarBuscador()"', $html);
        $this->assertSame(1, substr_count($html, '$refs.search?.focus()'), 'solo dentro de enfocarBuscador(): el resto lo llama');
        $this->assertStringContainsString("enfocarBuscador() {\n            this.\$nextTick(() => this.\$refs.search?.focus());\n        },", str_replace("\r\n", "\n", $html));
    }

    public function test_el_cambio_no_toca_los_demas_campos_numericos_del_cobro(): void
    {
        $html = $this->html('general');

        // la edición de cantidad y precio de la línea sigue con su teclado numérico: ahí la persona va a escribir
        $this->assertStringContainsString('<input class="qty-num" type="number" inputmode="numeric"', $html);
    }
}
