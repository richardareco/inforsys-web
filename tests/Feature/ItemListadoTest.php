<?php

namespace Tests\Feature;

use App\Filament\Resources\ItemResource\Pages\ListItems;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Concerns\BaseDelphiSqlite;
use Tests\TestCase;

/** Listado de Productos: las acciones deben verse siempre, sin tener que desplazarse a la derecha. */
class ItemListadoTest extends TestCase
{
    use BaseDelphiSqlite, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararDelphi();
        $this->prepararEmpresa();
        $this->producto('P1', ['descr' => 'LABIAL ROJO']);
    }

    public function test_el_listado_usa_todo_el_ancho(): void
    {
        $this->assertSame(MaxWidth::Full, (new ListItems)->getMaxContentWidth());
    }

    public function test_muestra_los_botones_ver_y_editar(): void
    {
        Livewire::test(ListItems::class)
            ->assertSuccessful()
            ->assertSee('LABIAL ROJO')
            ->assertSee('Ver')
            ->assertSee('Editar');
    }

    public function test_las_acciones_van_antes_de_las_columnas(): void
    {
        $html = Livewire::test(ListItems::class)->html();

        $this->assertLessThan(strpos($html, 'LABIAL ROJO'), strpos($html, 'Editar'), 'Editar aparece antes que los datos de la fila');
    }

    public function test_no_lista_filas_sin_codigo(): void
    {
        DB::connection('delphi')->table('items')->insert(['item' => '', 'descr' => 'FILA BASURA']);

        Livewire::test(ListItems::class)->assertSee('LABIAL ROJO')->assertDontSee('FILA BASURA');
    }

    public function test_el_modal_ver_muestra_todas_las_fotos_del_producto(): void
    {
        $a = $this->foto('P1');
        $b = $this->foto('P1');

        $html = Livewire::test(ListItems::class)->mountTableAction('view', 'P1')->html();

        $this->assertStringContainsString("/api/item-image/P1/{$a}", $html);
        $this->assertStringContainsString("/api/item-image/P1/{$b}", $html);
    }
}
