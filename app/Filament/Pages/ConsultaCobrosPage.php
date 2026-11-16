<?php

namespace App\Filament\Pages;

use App\Services\Caja\CobrosRealizados;
use App\Support\Modulos;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

/** Caja → Consulta de cobros: los cobros ya registrados, para revisarlos y reimprimir el ticket. */
class ConsultaCobrosPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Consulta de cobros';
    protected static ?string $navigationGroup = 'Caja';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $title           = 'Consulta de cobros';
    protected static ?string $slug            = 'consulta-cobros';
    protected static string  $view            = 'filament.pages.consulta-cobros-page';

    public string $periodo = 'hoy';
    public string $forma   = '';
    public string $buscar  = '';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user && $user->empresa_id && Modulos::permite($user, 'cobros');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getSubheading(): ?string
    {
        return 'Cobros registrados en caja. Podés reimprimir el ticket de cualquiera.';
    }

    protected function getViewData(): array
    {
        $periodo = array_key_exists($this->periodo, CobrosRealizados::PERIODOS) ? $this->periodo : 'hoy';
        $forma   = array_key_exists($this->forma, CobrosRealizados::FORMAS) ? $this->forma : '';

        return [
            'd'        => (new CobrosRealizados)->listar($periodo, $this->buscar, $forma),
            'periodos' => CobrosRealizados::PERIODOS,
            'formas'   => CobrosRealizados::FORMAS,
        ];
    }

    /** Dirección del ticket para reimprimir (el mismo comprobante de la venta, marcado como reimpresión). */
    public static function urlTicket(string $invnr): string
    {
        return url('/comprobante') . '/' . rawurlencode($invnr) . '/ticket?reimpresion=1';
    }
}
