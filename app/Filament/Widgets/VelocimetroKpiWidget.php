<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VelocimetroKpiWidget extends Widget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 1;
    protected static string $view = 'filament.widgets.velocimetro-kpi';

    public string $periodo    = 'mes';
    public float  $percentage = 0;
    public float  $actual     = 0;
    public float  $metaMonto  = 0;
    public string $mesLabel   = '';
    public bool   $tieneMeta  = false;

    public function mount(): void
    {
        $this->calcular();
    }

    public function updatedPeriodo(): void
    {
        $this->calcular();
    }

    private function calcular(): void
    {
        $empresaId        = Auth::user()->empresa_id;
        $this->mesLabel   = now()->isoFormat('MMMM YYYY');

        $metaMes = (float) (DB::table('metas')
            ->where('empresa_id', $empresaId)
            ->where('mes', now()->startOfMonth()->format('Y-m-d'))
            ->value('monto') ?? 0);

        if ($this->periodo === 'dia') {
            $this->actual    = (float) DB::connection('delphi')
                ->table('invo1')->where('flag', '4')
                ->whereDate('fecha', today())->sum('total');
            $this->metaMonto = $metaMes > 0 ? round($metaMes / 30, 2) : 0;
        } else {
            $this->actual    = (float) DB::connection('delphi')
                ->table('invo1')->where('flag', '4')
                ->whereBetween('fecha', [now()->startOfMonth(), now()])->sum('total');
            $this->metaMonto = $metaMes;
        }

        $this->tieneMeta  = $this->metaMonto > 0;
        $this->percentage = $this->tieneMeta
            ? min(100, round(($this->actual / $this->metaMonto) * 100, 1))
            : 0;
    }

    protected function getViewData(): array
    {
        return [
            'mesLabel'   => $this->mesLabel,
            'actual'     => $this->actual,
            'meta'       => $this->tieneMeta ? $this->metaMonto : null,
            'percentage' => $this->percentage,
            'periodo'    => $this->periodo,
        ];
    }
}
