<?php

namespace App\Filament\Pages;

use App\Services\Caja\CobroInvalido;
use App\Services\Caja\CobrosPendientes;
use App\Support\Modulos;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Caja: ventas que el vendedor dejó pendientes de cobro, para que el cajero las cobre con la forma de pago que
 * corresponda (efectivo, transferencia, tarjeta o mezcladas). El listado se actualiza solo cada 2 segundos.
 */
class CobrosPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Cobros';
    protected static ?string $navigationGroup = 'Caja';
    protected static ?int    $navigationSort  = 0;
    protected static ?string $title           = 'Cobros pendientes';
    protected static ?string $slug            = 'cobros';
    protected static string  $view            = 'filament.pages.cobros-page';

    public string $periodo = 'hoy';
    public string $buscar  = '';

    /** Venta que se está por cobrar (con sus productos) y resultado del último cobro. */
    public ?array $seleccion = null;
    public ?array $cobrado   = null;

    /** Se actualizan en cada refresco: el navegador avisa cuando aparece una venta nueva. */
    public string $ultima   = '';
    public int    $cantidad = 0;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user && $user->empresa_id && Modulos::permite($user, 'cobros');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('consulta')->label('Consulta de cobros')->icon('heroicon-o-clipboard-document-list')->color('gray')
                ->url(fn () => ConsultaCobrosPage::getUrl()),
        ];
    }

    public function getHeading(): string
    {
        return 'Cobros pendientes';
    }

    public function getSubheading(): ?string
    {
        return 'Ventas que dejó el vendedor y esperan su cobro en caja.';
    }

    protected function getViewData(): array
    {
        $periodo = array_key_exists($this->periodo, CobrosPendientes::PERIODOS) ? $this->periodo : 'hoy';
        $d = (new CobrosPendientes)->listar($periodo, $this->buscar);

        $this->ultima   = $d['ultima'];
        $this->cantidad = count($d['ventas']);

        return [
            'ventas'      => $d['ventas'],
            'totalLista'  => $d['total'],
            'periodos'    => CobrosPendientes::PERIODOS,
            'cajaEnPos'   => (bool) (Auth::user()?->empresa?->modulo_caja_enabled ?? true),
        ];
    }

    public function abrirCobro(string $invnr): void
    {
        $this->cobrado = null;
        $venta = (new CobrosPendientes)->venta($invnr);

        if (!$venta) {
            Notification::make()->title('Esa venta ya no está pendiente')->body('Pudo haberla cobrado otro cajero.')->warning()->send();
            $this->seleccion = null;

            return;
        }

        $this->seleccion = $venta;
    }

    public function cerrarCobro(): void
    {
        $this->seleccion = null;
    }

    public function cerrarCobrado(): void
    {
        $this->cobrado = null;
    }

    /** @param array{efectivo?: mixed, transferencia?: mixed, tarjeta?: mixed} $pagos */
    public function cobrar(array $pagos): void
    {
        abort_unless(static::canAccess(), 403);

        if (!$this->seleccion) {
            return;
        }

        $num = fn ($v) => is_numeric($v) ? (float) $v : 0.0;
        $invnr = $this->seleccion['invnr'];

        try {
            $r = (new CobrosPendientes)->cobrar($invnr, $num($pagos['efectivo'] ?? 0), $num($pagos['transferencia'] ?? 0), $num($pagos['tarjeta'] ?? 0));
        } catch (CobroInvalido $e) {
            Notification::make()->title('No se pudo registrar el cobro')->body($e->getMessage())->danger()->send();
            // si ya no está pendiente no tiene sentido seguir con ella
            if (!(new CobrosPendientes)->venta($invnr)) {
                $this->seleccion = null;
            }

            return;
        } catch (\Throwable $e) {
            report($e);
            Notification::make()->title('Error al registrar el cobro')->body('No se guardó nada. Intentá de nuevo.')->danger()->send();

            return;
        }

        Log::info('Cobro en caja', ['venta' => $invnr, 'cajanr' => $r['cajanr'], 'total' => $r['total'], 'usuario' => Auth::id()]);

        $this->cobrado = [
            'numero'        => $this->seleccion['numero'],
            'cliente'       => $this->seleccion['cliente'],
            'total'         => $r['total'],
            'efectivo'      => $r['efectivo'],
            'transferencia' => $r['transferencia'],
            'tarjeta'       => $r['tarjeta'],
            'vuelto'        => $r['vuelto'],
            'ticket'        => url('/comprobante') . '/' . $invnr . '/ticket?pago=' . (int) ($r['recibido'] ?: $r['total']) . '&medio=' . $r['principal'],
        ];
        $this->seleccion = null;
    }
}
