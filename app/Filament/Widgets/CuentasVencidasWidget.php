<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class CuentasVencidasWidget extends Widget
{
    protected static ?int $sort = 10;
    protected int | string | array $columnSpan = 1;
    protected static string $view = 'filament.widgets.cuentas-vencidas';

    protected function getViewData(): array
    {
        $db  = DB::connection('delphi');
        $hoy = today()->toDateString();

        // Fecha de vencimiento efectiva: si no hay vencimiento válido se usa la fecha de la venta
        $vcto = "CASE WHEN invo1.Fecha_vcto IS NULL OR invo1.Fecha_vcto < '1900-01-01' THEN invo1.fecha ELSE invo1.Fecha_vcto END";

        $pendientes = fn () => $db->table('invo1')->where('invo1.flag', '4')->where('invo1.saldo', '>', 0);

        $resumen = $pendientes()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN $vcto >= ? THEN invo1.saldo ELSE 0 END), 0) AS por_vencer,
                COALESCE(SUM(CASE WHEN $vcto < ? THEN invo1.saldo ELSE 0 END), 0) AS vencido,
                COALESCE(SUM(CASE WHEN DATEDIFF(?, $vcto) BETWEEN 1 AND 30 THEN invo1.saldo ELSE 0 END), 0) AS d30,
                COALESCE(SUM(CASE WHEN DATEDIFF(?, $vcto) BETWEEN 31 AND 60 THEN invo1.saldo ELSE 0 END), 0) AS d60,
                COALESCE(SUM(CASE WHEN DATEDIFF(?, $vcto) BETWEEN 61 AND 90 THEN invo1.saldo ELSE 0 END), 0) AS d90,
                COALESCE(SUM(CASE WHEN DATEDIFF(?, $vcto) > 90 THEN invo1.saldo ELSE 0 END), 0) AS d90mas
            ", [$hoy, $hoy, $hoy, $hoy, $hoy, $hoy])
            ->first();

        $clientes = $pendientes()
            ->join('cliente', 'cliente.custnr', '=', 'invo1.custnr')
            ->whereRaw("$vcto < ?", [$hoy])
            ->selectRaw("cliente.custnr, MAX(cliente.custname) AS custname, SUM(invo1.saldo) AS deuda, COUNT(*) AS facturas, MAX(DATEDIFF(?, $vcto)) AS dias", [$hoy])
            ->groupBy('cliente.custnr')
            ->orderByDesc('deuda')
            ->limit(8)
            ->get();

        $vencido   = (float) ($resumen->vencido ?? 0);
        $porVencer = (float) ($resumen->por_vencer ?? 0);

        $tramos = [
            ['label' => '1 - 30 días',  'monto' => (float) ($resumen->d30 ?? 0),    'color' => '#f59e0b'],
            ['label' => '31 - 60 días', 'monto' => (float) ($resumen->d60 ?? 0),    'color' => '#f97316'],
            ['label' => '61 - 90 días', 'monto' => (float) ($resumen->d90 ?? 0),    'color' => '#ef4444'],
            ['label' => 'Más de 90',    'monto' => (float) ($resumen->d90mas ?? 0), 'color' => '#b91c1c'],
        ];

        return compact('vencido', 'porVencer', 'tramos', 'clientes');
    }
}
