<?php

namespace Tests\Feature;

use App\Filament\Pages\TiendaOnlinePage;
use App\Models\EcomConfig;
use App\Models\User;
use App\Support\Modulos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BaseDelphiSqlite;
use Tests\TestCase;

/** Módulo Tienda online: el vendedor solo copia/abre el enlace; la configuración es de administradores. */
class TiendaEnlaceTest extends TestCase
{
    use BaseDelphiSqlite, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararDelphi();
        $this->prepararEmpresa();
        $this->usuario->update(['accesos' => ['ecommerce' => true]]);   // administrador
    }

    private function usuario(string $rol, bool $acceso = true): User
    {
        return User::create([
            'name' => ucfirst($rol), 'email' => $rol . uniqid() . '@test.com', 'password' => bcrypt('x'), 'empresa_id' => $this->empresa->id,
            'role' => $rol, 'accesos' => ['ecommerce' => $acceso],
        ]);
    }

    private function tienda(bool $activa = true): EcomConfig
    {
        return EcomConfig::create(['empresa_id' => $this->empresa->id, 'activo' => $activa, 'slug' => 'boutique', 'nombre' => 'Mi Boutique']);
    }

    // ───────────── Vendedor: solo el enlace ─────────────

    public function test_el_vendedor_ve_copiar_link_y_abrir_tienda_con_el_enlace_de_su_empresa(): void
    {
        $this->tienda();
        $this->actingAs($this->usuario('vendedor'));

        Livewire::test(TiendaOnlinePage::class)
            ->assertSuccessful()
            ->assertSee('Copiar link')
            ->assertSee('Abrir tienda')
            ->assertSee('Mi Boutique')
            ->assertSee(url('/tienda/boutique'), false)
            ->assertSee('Activa');
    }

    public function test_el_vendedor_no_ve_la_configuracion_ni_el_boton_guardar(): void
    {
        $this->tienda();
        $this->actingAs($this->usuario('vendedor'));

        Livewire::test(TiendaOnlinePage::class)
            ->assertDontSee('Guardar tienda')
            ->assertDontSee('Mostrar la información del producto')
            ->assertDontSee('Mostrar precios')
            ->assertDontSee('Carrito de compras');
    }

    public function test_el_vendedor_no_puede_guardar_la_configuracion_aunque_lo_intente(): void
    {
        $tienda = $this->tienda();
        $this->actingAs($this->usuario('vendedor'));

        Livewire::test(TiendaOnlinePage::class)
            ->set('data.nombre', 'Hackeada')
            ->set('data.slug', 'otra-direccion')
            ->call('save')
            ->assertForbidden();

        $tienda->refresh();
        $this->assertSame('Mi Boutique', $tienda->nombre);
        $this->assertSame('boutique', $tienda->slug);
    }

    public function test_abrir_la_pagina_como_vendedor_no_crea_una_tienda(): void
    {
        $this->actingAs($this->usuario('vendedor'));

        Livewire::test(TiendaOnlinePage::class)->assertSee('Todavía no hay una tienda online');

        $this->assertSame(0, EcomConfig::count());
    }

    public function test_con_la_tienda_apagada_el_vendedor_lo_ve_y_se_le_indica_que_pida_activarla(): void
    {
        $this->tienda(activa: false);
        $this->actingAs($this->usuario('vendedor'));

        Livewire::test(TiendaOnlinePage::class)
            ->assertSee('Apagada')
            ->assertSee('Pedile a tu administrador que la active')
            ->assertDontSee('Activala más abajo');
    }

    public function test_un_vendedor_sin_el_modulo_no_entra(): void
    {
        $this->actingAs($this->usuario('vendedor', acceso: false));

        $this->assertFalse(TiendaOnlinePage::canAccess());
        $this->assertFalse(TiendaOnlinePage::shouldRegisterNavigation());
    }

    public function test_el_vendedor_aparece_en_el_menu_si_tiene_el_modulo(): void
    {
        $this->actingAs($this->usuario('vendedor'));

        $this->assertTrue(TiendaOnlinePage::canAccess());
        $this->assertFalse(TiendaOnlinePage::puedeConfigurar());
    }

    // ───────────── Administradores: configuración + enlace ─────────────

    public function test_el_administrador_ve_el_enlace_y_la_configuracion(): void
    {
        $this->tienda();
        $this->actingAs($this->usuario);

        Livewire::test(TiendaOnlinePage::class)
            ->assertSuccessful()
            ->assertSee('Copiar link')
            ->assertSee('Abrir tienda')
            ->assertSee(url('/tienda/boutique'), false)
            ->assertSee('Guardar tienda')
            ->assertSee('Mostrar la información del producto');
        $this->assertTrue(TiendaOnlinePage::puedeConfigurar());
    }

    public function test_el_superadministrador_tambien_configura(): void
    {
        $super = User::create([
            'name' => 'Super', 'email' => 'super@test.com', 'password' => bcrypt('x'), 'empresa_id' => $this->empresa->id, 'role' => 'superadmin',
        ]);
        $this->actingAs($super);

        Livewire::test(TiendaOnlinePage::class)->assertSee('Guardar tienda')->assertSee('Copiar link');
        $this->assertTrue(TiendaOnlinePage::puedeConfigurar());
    }

    public function test_el_administrador_puede_guardar_y_el_primer_ingreso_crea_la_tienda(): void
    {
        $this->actingAs($this->usuario);

        $t = Livewire::test(TiendaOnlinePage::class);
        $this->assertSame(1, EcomConfig::count(), 'el administrador crea la configuración al entrar, como antes');

        $t->fillForm(['nombre' => 'Boutique Linda'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('Boutique Linda', EcomConfig::first()->nombre);
    }

    public function test_con_la_tienda_apagada_el_administrador_ve_como_activarla(): void
    {
        $this->tienda(activa: false);
        $this->actingAs($this->usuario);

        Livewire::test(TiendaOnlinePage::class)->assertSee('Apagada')->assertSee('Activala más abajo');
    }

    public function test_otros_roles_sin_permiso_de_administracion_tampoco_configuran(): void
    {
        $this->actingAs($this->usuario('cajero'));

        $this->assertFalse(TiendaOnlinePage::puedeConfigurar());
    }

    // ───────────── Accesos ─────────────

    public function test_los_vendedores_reciben_el_acceso_a_la_tienda_por_defecto(): void
    {
        $this->assertContains('ecommerce', Modulos::sugeridos('vendedor'));
        $this->assertNotContains('ecommerce', Modulos::sugeridos('cajero'));
    }
}
