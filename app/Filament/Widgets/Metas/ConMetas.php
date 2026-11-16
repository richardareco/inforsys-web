<?php

namespace App\Filament\Widgets\Metas;

use App\Services\Dashboard\MetasVendedores;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

/**
 * Base de los widgets de la pestaña "Metas por vendedor" del dashboard administrativo. Son de la empresa entera, por eso
 * no se muestran a los vendedores (ellos ven su propio dashboard).
 */
trait ConMetas
{
    use InteractsWithPageFilters;

    public static function canView(): bool
    {
        $u = auth()->user();

        return $u && !$u->isVendedor() && $u->empresa_id;
    }

    /** Mes elegido en el filtro de la pestaña (por defecto el actual). */
    protected function mesElegido(): Carbon
    {
        try {
            return Carbon::parse($this->filters['mes_metas'] ?? now()->startOfMonth()->toDateString())->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }

    protected function datos(): array
    {
        return (new MetasVendedores((int) auth()->user()->empresa_id))->delMes($this->mesElegido());
    }
}
