<?php

namespace Tests\Feature;

use App\Filament\Pages\ConfiguracionesVentasPage;
use App\Filament\Pages\PosPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BaseDelphiSqlite;
use Tests\TestCase;

/** Tipos de punto de venta: cada empresa usa la vista de su tipo y la elige en Configuraciones. */
class PosTipoTest extends TestCase
{
    use BaseDelphiSqlite, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararDelphi();
        $this->prepararEmpresa();
    }

    public function test_cada_tipo_usa_su_vista(): void
    {
        foreach ([
            'general'      => 'filament.pages.pos-page',
            'supermercado' => 'filament.pages.pos-page-minimal',
            'catalogo'     => 'filament.pages.pos-page-catalogo',
        ] as $tipo => $vista) {
            $this->cambiarTipo($tipo);
            $this->assertSame($vista, (new PosPage)->getView(), "tipo {$tipo}");
        }
    }

    public function test_solo_el_catalogo_usa_todo_el_ancho_de_la_pantalla(): void
    {
        $this->cambiarTipo('catalogo');
        $this->assertSame(\Filament\Support\Enums\MaxWidth::Full, (new PosPage)->getMaxContentWidth());

        foreach (['general', 'supermercado'] as $tipo) {
            $this->cambiarTipo($tipo);
            $this->assertNull((new PosPage)->getMaxContentWidth(), "tipo {$tipo} no cambia");
        }
    }

    public function test_un_tipo_desconocido_cae_en_el_pos_estandar(): void
    {
        $this->cambiarTipo('inexistente');

        $this->assertSame('filament.pages.pos-page', (new PosPage)->getView());
    }

    public function test_la_lista_de_tipos_incluye_el_catalogo_con_su_etiqueta(): void
    {
        $this->assertSame('Catálogo / Boutique (productos con fotos y ficha)', PosPage::TIPOS['catalogo']);
        $this->assertSame(['general', 'supermercado', 'catalogo'], array_keys(PosPage::TIPOS));
    }

    public function test_configuraciones_muestra_el_tipo_actual_y_lo_guarda(): void
    {
        $this->cambiarTipo('supermercado');

        Livewire::test(ConfiguracionesVentasPage::class)
            ->assertFormSet(['tipo' => 'supermercado'])
            ->fillForm(['tipo' => 'catalogo'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('catalogo', $this->empresa->fresh()->tipo);
    }

    public function test_configuraciones_no_acepta_un_tipo_inventado(): void
    {
        $this->cambiarTipo('general');

        Livewire::test(ConfiguracionesVentasPage::class)
            ->fillForm(['tipo' => 'hack'])
            ->call('save')
            ->assertHasFormErrors(['tipo']);

        $this->assertSame('general', $this->empresa->fresh()->tipo);
    }

    public function test_guardar_otras_opciones_no_cambia_el_tipo(): void
    {
        $this->cambiarTipo('catalogo');

        Livewire::test(ConfiguracionesVentasPage::class)
            ->fillForm(['permitir_venta_sin_stock' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $e = $this->empresa->fresh();
        $this->assertSame('catalogo', $e->tipo);
        $this->assertTrue((bool) $e->permitir_venta_sin_stock);
    }
}
