<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class StockBajoWidget extends Widget
{
    protected static ?int $sort = 9;
    protected int | string | array $columnSpan = 1;
    protected static string $view = 'filament.widgets.stock-bajo';

    protected function getViewData(): array
    {
        $db = DB::connection('delphi');

        $vendidos = $db->table('invo2')
            ->where('flag', '3')
            ->where('fecha', '>=', today()->subDays(30)->toDateString())
            ->selectRaw('item, SUM(qty) AS vendido')
            ->groupBy('item');

        // Bajo el mínimo configurado, o agotado pero con ventas en los últimos 30 días
        $base = fn () => $db->table('items')
            ->leftJoinSub($vendidos, 'v', 'v.item', '=', 'items.item')
            ->where(function ($q) {
                $q->where(fn ($w) => $w->where('items.stk_minimo', '>', 0)->whereColumn('items.stock', '<=', 'items.stk_minimo'))
                  ->orWhere(fn ($w) => $w->where('items.stock', '<=', 0)->where('v.vendido', '>', 0));
            });

        $items = $base()
            ->selectRaw('items.item, items.descr, items.stock, items.stk_minimo, COALESCE(v.vendido, 0) AS vendido')
            ->orderByRaw('(items.stk_minimo > 0 AND items.stock <= items.stk_minimo AND items.stock > 0) DESC')
            ->orderByDesc('vendido')
            ->limit(8)
            ->get();

        $agotados = (int) $base()->where('items.stock', '<=', 0)->count();
        $bajos    = (int) $base()->where('items.stock', '>', 0)->count();

        return compact('items', 'agotados', 'bajos');
    }
}
