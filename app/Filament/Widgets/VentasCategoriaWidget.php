<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class VentasCategoriaWidget extends Widget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 11;
    protected int | string | array $columnSpan = 1;
    protected static string $view = 'filament.widgets.ventas-categoria';

    private function getDateRange(): array
    {
        return match ($this->filters['period'] ?? 'dia') {
            'ayer'   => [today()->subDay(), today()->subDay()],
            'semana' => [now()->startOfWeek(), now()->endOfWeek()],
            'mes'    => [now()->startOfMonth(), now()->endOfMonth()],
            'año'    => [now()->startOfYear(), now()->endOfYear()],
            default  => [today(), today()],
        };
    }

    protected function getViewData(): array
    {
        [$start, $end] = $this->getDateRange();

        $filas = DB::connection('delphi')
            ->table('invo2')
            ->join('items', 'items.item', '=', 'invo2.item')
            ->where('invo2.flag', '3')
            ->whereBetween('invo2.fecha', [$start, $end])
            ->selectRaw('items.grupnr, (SELECT g.descr FROM grupo g WHERE g.grupnr = items.grupnr LIMIT 1) AS categoria, SUM(invo2.qty * invo2.precio) AS ingresos')
            ->groupBy('items.grupnr')
            ->orderByDesc('ingresos')
            ->get();

        $total = (float) $filas->sum('ingresos');
        $paleta = ['#6366f1', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#a855f7'];

        $top = $filas->take(6)->values();
        $resto = (float) $filas->slice(6)->sum('ingresos');

        $categorias = $top->map(fn ($f, $i) => [
            'nombre' => trim((string) ($f->categoria ?? '')) !== '' ? trim($f->categoria) : 'Sin categoría',
            'monto'  => (float) $f->ingresos,
            'pct'    => $total > 0 ? (float) $f->ingresos / $total * 100 : 0,
            'color'  => $paleta[$i % count($paleta)],
        ])->all();

        if ($resto > 0) {
            $categorias[] = [
                'nombre' => 'Otras categorías',
                'monto'  => $resto,
                'pct'    => $total > 0 ? $resto / $total * 100 : 0,
                'color'  => '#94a3b8',
            ];
        }

        return compact('categorias', 'total');
    }
}
