<?php

namespace App\Filament\Resources\WaPedidoResource\Pages;

use App\Filament\Resources\WaPedidoResource;
use App\Models\WaPedido;
use Carbon\Carbon;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

/** Pedidos de WhatsApp con forma de panel: período (hoy, ayer, rango), totales por estado y la lista. */
class ListWaPedidos extends ListRecords
{
    protected static string $resource = WaPedidoResource::class;

    public string $periodo = 'hoy';   // hoy | ayer | semana | mes | rango
    public ?string $desde = null;
    public ?string $hasta = null;

    public function getTitle(): string | Htmlable
    {
        return 'Pedidos de WhatsApp';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function setPeriodo(string $periodo): void
    {
        $this->periodo = in_array($periodo, ['hoy', 'ayer', 'semana', 'mes', 'rango'], true) ? $periodo : 'hoy';

        if ($this->periodo === 'rango' && !$this->desde) {
            $this->desde = today()->subDays(6)->toDateString();
            $this->hasta = today()->toDateString();
        }

        $this->resetPage();
    }

    public function updatedDesde(): void { $this->resetPage(); }
    public function updatedHasta(): void { $this->resetPage(); }

    /** @return array{0: Carbon, 1: Carbon} */
    public function rango(): array
    {
        return match ($this->periodo) {
            'ayer'   => [today()->subDay()->startOfDay(), today()->subDay()->endOfDay()],
            'semana' => [today()->startOfWeek()->startOfDay(), today()->endOfDay()],
            'mes'    => [today()->startOfMonth()->startOfDay(), today()->endOfDay()],
            'rango'  => $this->rangoPersonalizado(),
            default  => [today()->startOfDay(), today()->endOfDay()],
        };
    }

    private function rangoPersonalizado(): array
    {
        try {
            $d = $this->desde ? Carbon::parse($this->desde)->startOfDay() : today()->startOfDay();
            $h = $this->hasta ? Carbon::parse($this->hasta)->endOfDay() : today()->endOfDay();
        } catch (\Throwable) {
            return [today()->startOfDay(), today()->endOfDay()];
        }

        return $h->lt($d) ? [$h->copy()->startOfDay(), $d->copy()->endOfDay()] : [$d, $h];
    }

    private function enPeriodo(Builder $q): Builder
    {
        [$d, $h] = $this->rango();

        return $q->whereBetween('created_at', [$d, $h]);
    }

    private function base(): Builder
    {
        return $this->enPeriodo(WaPedidoResource::getEloquentQuery());
    }

    /** Totales por estado para las tarjetas del panel. */
    public function resumen(): array
    {
        $filas = $this->base()->selectRaw('estado, COUNT(*) AS n, COALESCE(SUM(total), 0) AS monto')->groupBy('estado')->get()->keyBy('estado');
        $de = fn (array $estados) => [
            'n'     => (int) collect($estados)->sum(fn ($e) => $filas[$e]->n ?? 0),
            'monto' => (float) collect($estados)->sum(fn ($e) => $filas[$e]->monto ?? 0),
        ];

        return [
            'pendiente' => $de(['pendiente', 'procesando']),
            'pagado'    => $de(['pagado']),
            'anulado'   => $de(['anulado']),
            'sin'       => $de(['borrador']),
        ];
    }

    public function etiquetaPeriodo(): string
    {
        [$d, $h] = $this->rango();

        return match ($this->periodo) {
            'hoy'  => 'Hoy, ' . $d->format('d/m/Y'),
            'ayer' => 'Ayer, ' . $d->format('d/m/Y'),
            default => $d->isSameDay($h) ? $d->format('d/m/Y') : $d->format('d/m/Y') . ' al ' . $h->format('d/m/Y'),
        };
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.wa-pedidos-panel', ['pagina' => $this, 'resumen' => $this->resumen()]);
    }

    protected function modifyQueryWithActiveTab(Builder $query): Builder
    {
        return parent::modifyQueryWithActiveTab($this->enPeriodo($query));
    }

    public function getTabs(): array
    {
        $r = $this->resumen();

        return [
            'pendiente' => Tab::make('Pendientes')->badge($r['pendiente']['n'])->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('estado', ['pendiente', 'procesando'])),
            'pagado' => Tab::make('Pagados')->badge($r['pagado']['n'])->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', 'pagado')),
            'anulado' => Tab::make('Anulados')->badge($r['anulado']['n'])->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', 'anulado')),
            'sin_confirmar' => Tab::make('Sin confirmar')->badge($r['sin']['n'])->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', 'borrador')),
            'todos' => Tab::make('Todos'),
        ];
    }
}
