<?php

namespace Tests\Feature;

use App\Filament\Resources\ItemResource\Pages\CreateItem;
use App\Filament\Resources\ItemResource\Pages\EditItem;
use App\Http\Controllers\ItemImageController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Concerns\BaseDelphiSqlite;
use Tests\TestCase;

/** Formulario de Productos (sección "Ficha del producto") y ruta de fotos por nro. */
class ItemFichaFormularioTest extends TestCase
{
    use BaseDelphiSqlite, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararDelphi();
        $this->prepararEmpresa();
        $this->producto('P1', ['descr' => 'BASE FLUIDA']);
    }

    private function fila(string $item = 'P1'): ?object
    {
        return DB::connection('delphi')->table('items_ficha')->where('item', $item)->first();
    }

    public function test_la_seccion_ficha_aparece_si_la_base_tiene_la_tabla(): void
    {
        Livewire::test(EditItem::class, ['record' => 'P1'])
            ->assertSuccessful()
            ->assertSee('Datos del producto')
            ->assertSee('Información del producto')
            ->assertSee('Ficha del producto')
            ->assertSee('Modo de uso')
            ->assertSee('Tonos / colores');
    }

    public function test_sin_la_tabla_items_ficha_no_aparece_la_ficha_ni_rompe_el_formulario(): void
    {
        $this->prepararDelphi(conFicha: false);
        $this->producto('P1', ['descr' => 'BASE FLUIDA']);

        Livewire::test(EditItem::class, ['record' => 'P1'])
            ->assertSuccessful()
            ->assertSee('Información del producto')
            ->assertDontSee('Ficha del producto')
            ->call('save')
            ->assertHasNoFormErrors();
    }

    public function test_al_crear_un_producto_se_puede_cargar_su_informacion(): void
    {
        Livewire::test(CreateItem::class)
            ->assertSee('Información del producto')
            ->fillForm([
                'descr' => 'LABIAL NUEVO', 'precio3' => 15000,
                'ficha.marca' => 'Natura', 'ficha.modo_uso' => 'Aplicar sobre los labios',
                'ficha.tonos' => [['nombre' => 'Rojo', 'color' => '#cc0000']],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $item = DB::connection('delphi')->table('items')->where('descr', 'LABIAL NUEVO')->value('item');
        $this->assertNotNull($item);
        $f = $this->fila($item);
        $this->assertSame('Natura', $f->marca);
        $this->assertSame('Aplicar sobre los labios', $f->modo_uso);
        $this->assertSame([['nombre' => 'Rojo', 'color' => '#cc0000']], json_decode($f->tonos, true));
    }

    public function test_guardar_sin_completar_la_ficha_no_crea_una_fila_vacia(): void
    {
        Livewire::test(EditItem::class, ['record' => 'P1'])->call('save')->assertHasNoFormErrors();

        $this->assertNull($this->fila());
    }

    public function test_completar_la_ficha_la_crea_y_editarla_no_duplica(): void
    {
        Livewire::test(EditItem::class, ['record' => 'P1'])
            ->fillForm([
                'ficha.marca' => 'Mary Kay', 'ficha.contenido' => '30 ml', 'ficha.beneficios' => 'Hidrata “24h”',
                'ficha.destacado' => true,
                'ficha.tonos' => [['nombre' => 'Nude', 'color' => '#d8a48f']],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $f = $this->fila();
        $this->assertSame('Mary Kay', $f->marca);
        $this->assertSame('Hidrata "24h"', $f->beneficios, 'las comillas curvas pasan a rectas (la tabla es latin1)');
        $this->assertSame(1, (int) $f->destacado);
        $this->assertSame([['nombre' => 'Nude', 'color' => '#d8a48f']], json_decode($f->tonos, true));
        $this->assertNotNull($f->mod_fecha);

        Livewire::test(EditItem::class, ['record' => 'P1'])
            ->assertFormSet(['ficha.marca' => 'Mary Kay'])
            ->fillForm(['ficha.marca' => 'Mary Kay 2'])
            ->call('save');

        $this->assertSame(1, DB::connection('delphi')->table('items_ficha')->where('item', 'P1')->count());
        $this->assertSame('Mary Kay 2', $this->fila()->marca);
    }

    public function test_cada_tono_exige_nombre_y_color(): void
    {
        Livewire::test(EditItem::class, ['record' => 'P1'])
            ->fillForm(['ficha.marca' => 'X', 'ficha.tonos' => [['nombre' => '', 'color' => '']]])
            ->call('save')
            ->assertHasFormErrors();

        $this->assertNull($this->fila());
    }

    // ───────────── Fotos por nro ─────────────

    private function pedirFoto(string $item, mixed $nro = null)
    {
        return app(ItemImageController::class)->show($item, $nro);
    }

    public function test_sin_nro_devuelve_la_primera_foto(): void
    {
        $a = $this->foto('P1', "\x89PNG...primera");
        $this->foto('P1', "\xFF\xD8\xFFsegunda");

        $r = $this->pedirFoto('P1');

        $this->assertSame(200, $r->getStatusCode());
        $this->assertSame('image/png', $r->headers->get('Content-Type'));
        $this->assertStringContainsString('primera', $r->getContent());
    }

    public function test_con_nro_devuelve_esa_foto(): void
    {
        $this->foto('P1', "\x89PNG...primera");
        $b = $this->foto('P1', "\xFF\xD8\xFFsegunda");

        $r = $this->pedirFoto('P1', (string) $b);

        $this->assertSame(200, $r->getStatusCode());
        $this->assertSame('image/jpeg', $r->headers->get('Content-Type'));
        $this->assertStringContainsString('segunda', $r->getContent());
    }

    public function test_detecta_webp(): void
    {
        $n = $this->foto('P1', 'RIFF' . 'abcd' . 'WEBPdatos');

        $this->assertSame('image/webp', $this->pedirFoto('P1', $n)->headers->get('Content-Type'));
    }

    public function test_un_nro_de_otro_producto_inexistente_o_no_numerico_da_404(): void
    {
        $this->producto('P2');
        $ajena = $this->foto('P2');
        $this->foto('P1');

        $this->assertSame(404, $this->pedirFoto('P1', $ajena)->getStatusCode(), 'foto de otro producto');
        $this->assertSame(404, $this->pedirFoto('P1', '99999')->getStatusCode(), 'nro inexistente');
        $this->assertSame(404, $this->pedirFoto('P1', 'abc')->getStatusCode(), 'no numérico');
        $this->assertSame(404, $this->pedirFoto('P1', '1 OR 1=1')->getStatusCode(), 'inyección');
        $this->assertSame(404, $this->pedirFoto('SIN-FOTO')->getStatusCode(), 'producto sin foto');
    }

    public function test_la_respuesta_trae_cache_privado_y_nosniff(): void
    {
        $this->foto('P1');

        $r = $this->pedirFoto('P1');

        $this->assertStringContainsString('max-age=86400', $r->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', $r->headers->get('Cache-Control'));
        $this->assertSame('nosniff', $r->headers->get('X-Content-Type-Options'));
    }

    public function test_la_ruta_acepta_nro_opcional_y_rechaza_valores_raros(): void
    {
        $rutas = app('router')->getRoutes();

        $this->assertSame('item-image.show', $rutas->match(Request::create('/api/item-image/P1'))->getName());
        $this->assertSame('item-image.show', $rutas->match(Request::create('/api/item-image/P1/12'))->getName());
        $this->assertStringEndsWith('/api/item-image/P1', route('item-image.show', ['item' => 'P1']), 'los enlaces viejos siguen igual');
        $this->assertStringEndsWith('/api/item-image/P1/5', route('item-image.show', ['item' => 'P1', 'nro' => 5]));

        foreach (['/api/item-image/P1/abc', '/api/item-image/P1/1/2', '/api/item-image/' . str_repeat('X', 30)] as $url) {
            try {
                $rutas->match(Request::create($url));
                $this->fail("{$url} no debería coincidir con la ruta");
            } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                $this->addToAssertionCount(1);
            }
        }
    }
}
