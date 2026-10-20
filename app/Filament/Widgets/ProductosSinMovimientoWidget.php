<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class ProductosSinMovimientoWidget extends Widget
{
    protected static ?int $sort = 12;
    protected int | string | array $columnSpan = 1;
    protected static string $view = 'filament.widgets.productos-sin-movimiento';

    public const DIAS = 30;

    protected function getViewData(): array
    {
        $db    = DB::connection('delphi');
        $desde = today()->subDays(self::DIAS)->toDateString();

        $vendidos = $db->table('invo2')
            ->where('flag', '3')
            ->where('fecha', '>=', $desde)
            ->select('item')
            ->distinct();

        $base = fn () => $db->table('items')
            ->where('items.stock', '>', 0)
            ->whereNotIn('items.item', $vendidos);

        $resumen = $base()
            ->selectRaw('COUNT(*) AS cantidad, COALESCE(SUM(items.stock * items.costo), 0) AS capital')
            ->first();

        $items = $base()
            ->selectRaw('items.item, items.descr, items.stock, items.costo, (items.stock * items.costo) AS capital')
            ->orderByDesc('capital')
            ->limit(8)
            ->get();

        return [
            'dias'     => self::DIAS,
            'cantidad' => (int) ($resumen->cantidad ?? 0),
            'capital'  => (float) ($resumen->capital ?? 0),
            'items'    => $items,
        ];
    }
}
