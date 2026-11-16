<?php

namespace App\Filament\Widgets\Vendedor;

use App\Services\Dashboard\VentasVendedor;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

/**
 * Base de los widgets del dashboard del vendedor: solo se muestran a usuarios con rol de vendedor, y siempre con las
 * ventas de ESE vendedor (nunca las de la empresa).
 */
trait ConVendedor
{
    use InteractsWithPageFilters;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->isVendedor();
    }

    protected function vendedor(): ?VentasVendedor
    {
        return VentasVendedor::paraUsuario(auth()->user());
    }

    /** @return array{0: Carbon, 1: Carbon, 2: string} */
    protected function rango(): array
    {
        return VentasVendedor::rango($this->filters ?? []);
    }
}
