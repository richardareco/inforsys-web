<?php

namespace App\Filament\Widgets\Vendedor;

use Filament\Widgets\Widget;

/** Aviso para un usuario con rol de vendedor que todavía no está vinculado a un vendedor del sistema. */
class SinVendedorWidget extends Widget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.vendedor.sin-vendedor';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->isVendedor();
    }
}
