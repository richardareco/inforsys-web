<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-trophy class="h-5 w-5 text-amber-400" />
                <span>Top 5 Vendedores</span>
            </div>
        </x-slot>

        @php
            $rankStyles   = [0 => '#f59e0b', 1 => '#9ca3af', 2 => '#b45309'];
            $avatarColors = ['#3b82f6', '#8b5cf6', '#10b981', '#f97316', '#f43f5e'];
            $maxVentas    = (float) ($vendedores->max('total_ventas') ?? 0);
        @endphp

        <style>
            .vend-row {
                display: grid;
                grid-template-columns: 1.6rem 2.1rem minmax(0, 1.6fr) 4.5rem minmax(7rem, 1fr) minmax(8.5rem, auto);
                align-items: center;
                gap: .75rem;
                padding: .6rem .25rem;
                border-bottom: 1px solid rgba(148, 163, 184, .16);
                transition: background-color .15s ease;
            }
            .vend-row:last-child { border-bottom: none; }
            .vend-row:hover { background: rgba(99, 102, 241, .05); }
            .vend-rank { width: 1.5rem; height: 1.5rem; border-radius: 9999px; color: #fff; font-size: .72rem; font-weight: 800; display: grid; place-items: center; }
            .vend-avatar { width: 2.1rem; height: 2.1rem; border-radius: 9999px; color: #fff; font-size: .72rem; font-weight: 800; display: grid; place-items: center; }
            .vend-name { font-size: .88rem; font-weight: 700; line-height: 1.2; }
            .vend-bar { height: .32rem; border-radius: 9999px; background: rgba(148, 163, 184, .22); margin-top: .35rem; overflow: hidden; }
            .vend-bar > i { display: block; height: 100%; border-radius: 9999px; }
            .vend-count { text-align: right; font-size: .78rem; color: #64748b; white-space: nowrap; }
            .vend-total { text-align: right; font-size: .9rem; font-weight: 800; white-space: nowrap; }
            .vend-com { text-align: right; border-radius: .7rem; padding: .3rem .6rem; background: rgba(16, 185, 129, .1); white-space: nowrap; }
            .vend-com b { display: block; font-size: .8rem; font-weight: 800; color: #059669; line-height: 1.15; }
            .vend-com span { font-size: .62rem; color: #10b981; }
            .vend-head { padding: 0 .25rem .35rem; border-bottom: 1px solid rgba(148, 163, 184, .25); }
            .vend-head span { font-size: .62rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; }
            .vend-head .r { text-align: right; }

            @media (max-width: 720px) {
                .vend-head { display: none; }
                .vend-row { grid-template-columns: 1.6rem 2.1rem minmax(0, 1fr) auto; row-gap: .35rem; }
                .vend-count { display: none; }
                .vend-total { grid-column: 4; }
                .vend-com { grid-column: 3 / -1; justify-self: start; text-align: left; display: flex; gap: .5rem; align-items: baseline; }
                .vend-com b { display: inline; }
            }
        </style>

        @if ($vendedores->isEmpty())
            <div class="py-10 text-center">
                <x-heroicon-o-inbox class="mx-auto h-10 w-10 text-gray-300" />
                <p class="mt-2 text-sm text-gray-400">Sin datos para el período</p>
            </div>
        @else
            <div class="vend-head vend-row" style="border-bottom:1px solid rgba(148,163,184,.25);">
                <span>#</span>
                <span></span>
                <span>Vendedor</span>
                <span class="r">Ventas</span>
                <span class="r">Total vendido</span>
                <span class="r">Comisión</span>
            </div>

            @foreach ($vendedores as $i => $v)
                @php
                    $comision    = (float) $v->total_ventas * (float) $v->porc_comision / 100;
                    $rankColor   = $rankStyles[$i] ?? '#3b82f6';
                    $avatarColor = $avatarColors[$i % count($avatarColors)];
                    $initials    = strtoupper(mb_substr(trim($v->pername), 0, 2));
                    $pct         = $maxVentas > 0 ? (float) $v->total_ventas / $maxVentas * 100 : 0;
                @endphp
                <div class="vend-row">
                    <span class="vend-rank" style="background:{{ $rankColor }};">{{ $i + 1 }}</span>
                    <span class="vend-avatar" style="background:{{ $avatarColor }};">{{ $initials }}</span>

                    <div style="min-width:0;">
                        <p class="vend-name truncate text-gray-800 dark:text-gray-100">{{ $v->pername }}</p>
                        <div class="vend-bar"><i style="width:{{ number_format($pct, 1, '.', '') }}%;background:{{ $avatarColor }};"></i></div>
                    </div>

                    <span class="vend-count">{{ number_format($v->ventas_count, 0, ',', '.') }} ventas</span>
                    <span class="vend-total text-gray-800 dark:text-gray-100">Gs. {{ number_format($v->total_ventas, 0, ',', '.') }}</span>

                    <div class="vend-com">
                        <b>Gs. {{ number_format($comision, 0, ',', '.') }}</b>
                        <span>comisión ({{ number_format($v->porc_comision, 1, ',', '.') }}%)</span>
                    </div>
                </div>
            @endforeach
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
