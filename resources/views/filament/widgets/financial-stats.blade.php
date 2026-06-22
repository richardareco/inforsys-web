<x-filament-widgets::widget>
    @php
        $row1 = [
            [
                'label'   => 'Ventas Totales',
                'value'   => 'Gs. ' . number_format($ventas, 0, ',', '.'),
                'change'  => $ventasChange,
                'icon'    => 'heroicon-o-banknotes',
                'solidBg' => '#16a34a',
            ],
            [
                'label'   => 'Costo',
                'value'   => 'Gs. ' . number_format($costo, 0, ',', '.'),
                'change'  => $costoChange,
                'icon'    => 'heroicon-o-shopping-cart',
                'solidBg' => '#2563eb',
            ],
            [
                'label'   => 'Gastos',
                'value'   => 'Gs. ' . number_format($gastos, 0, ',', '.'),
                'change'  => $gastosChange,
                'icon'    => 'heroicon-o-receipt-percent',
                'solidBg' => '#7c3aed',
            ],
            [
                'label'   => 'Devoluciones',
                'value'   => 'Gs. ' . number_format($devoluciones, 0, ',', '.'),
                'change'  => $devolucionesChange,
                'icon'    => 'heroicon-o-arrow-uturn-left',
                'solidBg' => '#ea580c',
            ],
        ];

        $row2 = [
            [
                'label'   => 'Ganancia Neta',
                'value'   => 'Gs. ' . number_format($ganancia, 0, ',', '.'),
                'change'  => $gananciaChange,
                'icon'    => $ganancia >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down',
                'solidBg' => $ganancia >= 0 ? '#15803d' : '#dc2626',
            ],
            [
                'label'   => 'Cuentas a Cobrar',
                'value'   => 'Gs. ' . number_format($cobrar, 0, ',', '.'),
                'sub'     => number_format($cobrarCount, 0, ',', '.') . ' facturas pendientes',
                'icon'    => 'heroicon-o-arrow-down-circle',
                'solidBg' => '#0284c7',
            ],
            [
                'label'   => 'Cuentas a Pagar',
                'value'   => 'Gs. ' . number_format($pagar, 0, ',', '.'),
                'sub'     => number_format($pagarCount, 0, ',', '.') . ' facturas pendientes',
                'icon'    => 'heroicon-o-arrow-up-circle',
                'solidBg' => '#d97706',
            ],
            [
                'label'   => 'Créditos Cobrados',
                'value'   => 'Gs. ' . number_format($creditosCobrados, 0, ',', '.'),
                'icon'    => 'heroicon-o-currency-dollar',
                'solidBg' => '#0d9488',
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
