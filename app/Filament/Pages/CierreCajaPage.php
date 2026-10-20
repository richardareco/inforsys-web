<?php

namespace App\Filament\Pages;

use App\Traits\CheckUserAcceso;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class CierreCajaPage extends Page
{
    use CheckUserAcceso;

    protected static ?string $navigationIcon  = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'Cierre de Caja';
    protected static ?string $title           = 'Cierre de Caja';
    protected static ?string $navigationGroup = 'Caja';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $slug            = 'cierre-caja';
    protected static string  $view            = 'filament.pages.cierre-caja-page';

    public string $fecha = '';

    public function mount(): void
    {
        $this->fecha = today()->toDateString();
    }

    public function irHoy(): void
    {
        $this->fecha = today()->toDateString();
    }

    public function irAyer(): void
    {
        $this->fecha = today()->subDay()->toDateString();
    }

    public function updatedFecha(): void
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->fecha)) {
            $this->fecha = today()->toDateString();
        }
    }

    protected function getViewData(): array
    {
        return self::datos($this->fecha ?: today()->toDateString());
    }

    public static function datos(string $fecha): array
    {
        $db = DB::connection('delphi');

        // ── Ventas del día (cabecera de factura) ──
        $ventas = $db->table('invo1')
            ->where('flag', '4')
            ->where('fecha', $fecha)
            ->selectRaw('COUNT(*) AS cantidad, COALESCE(SUM(total),0) AS total, COALESCE(SUM(CASE WHEN acredito > 0 THEN total ELSE 0 END),0) AS credito')
            ->first();

        $ventasCantidad = (int) ($ventas->cantidad ?? 0);
        $ventasTotal    = (float) ($ventas->total ?? 0);
        $ventasCredito  = (float) ($ventas->credito ?? 0);
        $ventasContado  = $ventasTotal - $ventasCredito;

        // ── Cobros registrados en caja ──
        $caja = $db->table('cajareg')
            ->where('fecha', $fecha)
            ->where('flag', '!=', 'c')
            ->where(fn ($q) => $q->whereNull('es_devolucion')->orWhere('es_devolucion', 0))
            ->where(fn ($q) => $q->whereNull('salida_tesoreria')->orWhere('salida_tesoreria', 0))
            ->selectRaw('COUNT(*) AS movimientos,
                COALESCE(SUM(paygs),0) AS efectivo,
                COALESCE(SUM(paytr),0) AS transferencia,
                COALESCE(SUM(paytj),0) AS tarjeta,
                COALESCE(SUM(paycq),0) AS cheque,
                COALESCE(SUM(payot),0) AS otros')
            ->first();

        $efectivo      = (float) ($caja->efectivo ?? 0);
        $transferencia = (float) ($caja->transferencia ?? 0);
        $tarjeta       = (float) ($caja->tarjeta ?? 0);
        $cheque        = (float) ($caja->cheque ?? 0);
        $otros         = (float) ($caja->otros ?? 0);
        $totalCobrado  = $efectivo + $transferencia + $tarjeta + $cheque + $otros;

        $salidas = $db->table('cajareg')
            ->where('fecha', $fecha)
            ->where('flag', '!=', 'c')
            ->where(fn ($q) => $q->where('es_devolucion', 1)->orWhere('salida_tesoreria', 1))
            ->sum('total');

        // ── Comparación ventas vs caja ──
        $diferencia = $ventasContado - $totalCobrado;

        $metodos = [
            ['label' => 'Efectivo',      'monto' => $efectivo,      'color' => '#16a34a', 'bg' => '#dcfce7'],
            ['label' => 'Transferencia', 'monto' => $transferencia, 'color' => '#2563eb', 'bg' => '#dbeafe'],
            ['label' => 'Tarjeta',       'monto' => $tarjeta,       'color' => '#9333ea', 'bg' => '#f3e8ff'],
        ];
        if ($cheque > 0) {
            $metodos[] = ['label' => 'Cheque', 'monto' => $cheque, 'color' => '#d97706', 'bg' => '#fef3c7'];
        }
        if ($otros > 0) {
            $metodos[] = ['label' => 'Otros', 'monto' => $otros, 'color' => '#64748b', 'bg' => '#e2e8f0'];
        }
        foreach ($metodos as &$m) {
            $m['pct'] = $totalCobrado > 0 ? $m['monto'] / $totalCobrado * 100 : 0;
        }
        unset($m);

        // ── Detalle: ventas del día con su cobro ──
        $detalle = $db->table('invo1')
            ->leftJoin('cajareg', 'cajareg.cajanr', '=', 'invo1.cajanr')
            ->leftJoin('cliente', 'cliente.custnr', '=', 'invo1.custnr')
            ->where('invo1.flag', '4')
            ->where('invo1.fecha', $fecha)
            ->orderByDesc('invo1.invnr')
            ->limit(300)
            ->get([
                'invo1.invnr', 'invo1.hora', 'invo1.total', 'invo1.acredito', 'invo1.cajanr',
                'cliente.custname',
                'cajareg.paygs', 'cajareg.paytr', 'cajareg.paytj', 'cajareg.paycq', 'cajareg.payot',
            ])
            ->map(function ($r) {
                $metodo = '—';
                if ((float) $r->acredito > 0) {
                    $metodo = 'Crédito';
                } elseif ($r->cajanr) {
                    $partes = [];
                    if ((float) $r->paygs > 0) $partes[] = 'Efectivo';
                    if ((float) $r->paytr > 0) $partes[] = 'Transferencia';
                    if ((float) $r->paytj > 0) $partes[] = 'Tarjeta';
                    if ((float) $r->paycq > 0) $partes[] = 'Cheque';
                    if ((float) $r->payot > 0) $partes[] = 'Otros';
                    $metodo = $partes ? implode(' + ', $partes) : 'Caja';
                } else {
                    $metodo = 'Sin cobro en caja';
                }
                $r->metodo = $metodo;
                return $r;
            });

        $sinCobro = $detalle->filter(fn ($r) => $r->metodo === 'Sin cobro en caja')->count();

        return [
            'fechaTexto'     => ucfirst(Carbon::parse($fecha)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')),
            'esHoy'          => $fecha === today()->toDateString(),
            'ventasCantidad' => $ventasCantidad,
            'ventasTotal'    => $ventasTotal,
            'ventasContado'  => $ventasContado,
            'ventasCredito'  => $ventasCredito,
            'totalCobrado'   => $totalCobrado,
            'movimientos'    => (int) ($caja->movimientos ?? 0),
            'metodos'        => $metodos,
            'diferencia'     => $diferencia,
            'salidas'        => (float) $salidas,
            'detalle'        => $detalle,
            'sinCobro'       => $sinCobro,
        ];
    }

    public static function canAccess(): bool
    {
        return self::userHasAcceso('cierre_caja');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }
}
