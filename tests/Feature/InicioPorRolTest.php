<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\PosPage;
use App\Http\Middleware\RedirectVendorsToPos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Concerns\BaseDelphiSqlite;
use Tests\TestCase;

/** Al entrar al sistema cada rol cae en su pantalla de inicio, pero desde el menú se puede ir a cualquier módulo permitido. */
class InicioPorRolTest extends TestCase
{
    use BaseDelphiSqlite, RefreshDatabase;

    private User $vendedor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararDelphi();
        $this->prepararEmpresa();
        $this->vendedor = User::create([
            'name' => 'Vendedora', 'email' => 'v@test.com', 'password' => bcrypt('x'), 'empresa_id' => $this->empresa->id,
            'role' => 'vendedor', 'personal_id' => '001', 'accesos' => ['dashboard' => true, 'ventas' => true],
        ]);
    }

    private function visita(?string $referer, string $ruta = '/admin'): \Symfony\Component\HttpFoundation\Response
    {
        $request = Request::create(url($ruta), 'GET', [], [], [], $referer ? ['HTTP_REFERER' => $referer] : []);

        return (new RedirectVendorsToPos)->handle($request, fn () => response('dashboard'));
    }

    public function test_el_vendedor_al_entrar_cae_en_el_punto_de_venta(): void
    {
        $this->actingAs($this->vendedor);

        $r = $this->visita(null);

        $this->assertTrue($r->isRedirect(PosPage::getUrl()));
    }

    public function test_el_vendedor_recien_logueado_tambien_cae_en_el_punto_de_venta(): void
    {
        $this->actingAs($this->vendedor);

        $this->assertTrue($this->visita(url('/admin/login'))->isRedirect(PosPage::getUrl()));
    }

    public function test_el_vendedor_puede_abrir_su_dashboard_desde_el_menu(): void
    {
        $this->actingAs($this->vendedor);

        $r = $this->visita(url('/admin/pos-page'));

        $this->assertFalse($r->isRedirect(), 'viene de otra pantalla del panel: se respeta lo que pidió');
        $this->assertSame('dashboard', $r->getContent());
    }

    public function test_un_referer_de_otro_sitio_no_cuenta_como_venir_del_panel(): void
    {
        $this->actingAs($this->vendedor);

        $this->assertTrue($this->visita('https://otro-sitio.com/admin/pos-page')->isRedirect(PosPage::getUrl()));
        $this->assertTrue($this->visita(url('/tienda/boutique'))->isRedirect(PosPage::getUrl()));
    }

    public function test_el_administrador_entra_a_su_dashboard_sin_redirecciones(): void
    {
        $this->actingAs($this->usuario);
        $this->usuario->update(['accesos' => ['dashboard' => true, 'ventas' => true]]);

        $this->assertFalse($this->visita(null)->isRedirect());
        $this->assertFalse($this->visita(url('/admin/pos-page'))->isRedirect());
    }

    public function test_solo_actua_sobre_la_raiz_del_panel(): void
    {
        $this->actingAs($this->vendedor);

        $this->assertFalse($this->visita(null, '/admin/pos-page')->isRedirect());
    }

    public function test_el_dashboard_esta_en_el_menu_del_vendedor(): void
    {
        $this->actingAs($this->vendedor);

        $this->assertTrue(Dashboard::canAccess());
        $this->assertTrue(Dashboard::shouldRegisterNavigation());
    }

    public function test_el_menu_del_celular_incluye_inicio_para_el_vendedor(): void
    {
        $this->actingAs($this->vendedor);

        $html = view('filament.components.mobile-bottom-nav')->render();

        $this->assertStringContainsString('Inicio', $html);
        $this->assertStringContainsString('Vender', $html);
    }
}
