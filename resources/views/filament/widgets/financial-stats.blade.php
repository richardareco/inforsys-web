<x-filament-widgets::widget>
    @php
        $row1 = [
            [
                'label'     => 'Ventas Totales',
                'value'     => 'Gs. ' . number_format($ventas, 0, ',', '.'),
                'change'    => $ventasChange,
                'icon'      => 'heroicon-o-banknotes',
                'cardBg'    => 'rgba(22,163,74,0.14)',
                'iconBg'    => 'rgba(22,163,74,0.30)',
                'iconColor' => '#15803d',
                'badgeUp'   => true,
            ],
            [
                'label'     => 'Costo',
                'value'     => 'Gs. ' . number_format($costo, 0, ',', '.'),
                'change'    => $costoChange,
                'icon'      => 'heroicon-o-shopping-cart',
                'cardBg'    => 'rgba(37,99,235,0.13)',
                'iconBg'    => 'rgba(37,99,235,0.28)',
                'iconColor' => '#1d4ed8',
                'badgeUp'   => true,
            ],
            [
                'label'     => 'Gastos',
                'value'     => 'Gs. ' . number_format($gastos, 0, ',', '.'),
                'change'    => $gastosChange,
                'icon'      => 'heroicon-o-receipt-percent',
                'cardBg'    => 'rgba(124,58,237,0.13)',
                'iconBg'    => 'rgba(124,58,237,0.28)',
                'iconColor' => '#6d28d9',
                'badgeUp'   => true,
            ],
            [
                'label'     => 'Devoluciones',
                'value'     => 'Gs. ' . number_format($devoluciones, 0, ',', '.'),
                'change'    => $devolucionesChange,
                'icon'      => 'heroicon-o-arrow-uturn-left',
                'cardBg'    => 'rgba(234,88,12,0.13)',
                'iconBg'    => 'rgba(234,88,12,0.28)',
                'iconColor' => '#c2410c',
                'badgeUp'   => true,
            ],
        ];

        $row2 = [
            [
                'label'     => 'Ganancia Neta',
                'value'     => 'Gs. ' . number_format($ganancia, 0, ',', '.'),
                'change'    => $gananciaChange,
                'icon'      => $ganancia >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down',
                'cardBg'    => $ganancia >= 0 ? 'rgba(22,163,74,0.14)' : 'rgba(225,29,72,0.14)',
                'iconBg'    => $ganancia >= 0 ? 'rgba(22,163,74,0.30)' : 'rgba(225,29,72,0.30)',
                'iconColor' => $ganancia >= 0 ? '#15803d' : '#be123c',
                'badgeUp'   => true,
            ],
            [
                'label'     => 'Cuentas a Cobrar',
                'value'     => 'Gs. ' . number_format($cobrar, 0, ',', '.'),
                'sub'       => number_format($cobrarCount, 0, ',', '.') . ' facturas pendientes',
                'icon'      => 'heroicon-o-arrow-down-circle',
                'cardBg'    => 'rgba(2,132,199,0.13)',
                'iconBg'    => 'rgba(2,132,199,0.28)',
                'iconColor' => '#0369a1',
            ],
            [
                'label'     => 'Cuentas a Pagar',
                'value'     => 'Gs. ' . number_format($pagar, 0, ',', '.'),
                'sub'       => number_format($pagarCount, 0, ',', '.') . ' facturas pendientes',
                'icon'      => 'heroicon-o-arrow-up-circle',
                'cardBg'    => 'rgba(217,119,6,0.13)',
                'iconBg'    => 'rgba(217,119,6,0.28)',
                'iconColor' => '#b45309',
            ],
            [
                'label'     => 'Créditos Cobrados',
                'value'     => 'Gs. ' . number_format($creditosCobrados, 0, ',', '.'),
                'icon'      => 'heroicon-o-currency-dollar',
                'cardBg'    => 'rgba(13,148,136,0.13)',
                'iconBg'    => 'rgba(13,148,136,0.28)',
                'iconColor' => '#0f766e',
            ],
        ];
    @endphp

    <style>
        .stats-row {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        @media (min-width: 768px) {
            .stats-row { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
    </style>

    <div style="display:flex; flex-direction:column; gap:0.75rem;">

        <div class="stats-row">
            @foreach ($row1 as $card)
                @include('filament.widgets.partials.stat-card', ['card' => $card])
            @endforeach
        </div>

        <div class="stats-row">
            @foreach ($row2 as $card)
                @include('filament.widgets.partials.stat-card', ['card' => $card])
            @endforeach
        </div>

    </div>
</x-filament-widgets::widget>
