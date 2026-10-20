<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-squares-2x2 class="h-5 w-5 text-indigo-500" />
                <span>Ventas por categoría</span>
            </div>
        </x-slot>

        @if(count($categorias) === 0)
            <div class="py-8 text-center">
                <x-heroicon-o-inbox class="mx-auto h-8 w-8 text-gray-300" />
                <p class="mt-2 text-sm text-gray-400">Sin datos para el período</p>
            </div>
        @else
            {{-- Barra apilada con la composición total --}}
            <div style="display:flex;height:.7rem;border-radius:9999px;overflow:hidden;background:rgba(148,163,184,.2);margin-bottom:1rem;">
                @foreach($categorias as $c)
                    <div style="width:{{ number_format($c['pct'], 2, '.', '') }}%;background:{{ $c['color'] }};" title="{{ $c['nombre'] }}"></div>
                @endforeach
            </div>

            @foreach($categorias as $c)
                <div style="display:grid;grid-template-columns:1fr auto;gap:.2rem .75rem;align-items:center;padding:.5rem 0;border-bottom:1px solid rgba(148,163,184,.15);">
                    <div style="display:flex;align-items:center;gap:.5rem;min-width:0;">
                        <span style="width:.65rem;height:.65rem;border-radius:9999px;background:{{ $c['color'] }};flex-shrink:0;"></span>
                        <span class="truncate text-sm font-medium text-gray-700 dark:text-gray-200">{{ $c['nombre'] }}</span>
                    </div>
                    <div style="text-align:right;white-space:nowrap;">
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100">Gs. {{ number_format($c['monto'], 0, ',', '.') }}</span>
                        <span style="margin-left:.4rem;font-size:.72rem;font-weight:700;color:#64748b;">{{ number_format($c['pct'], 1, ',', '.') }}%</span>
                    </div>
                </div>
            @endforeach

            <div style="display:flex;justify-content:space-between;margin-top:.7rem;">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total</span>
                <span class="text-sm font-black text-gray-800 dark:text-gray-100">Gs. {{ number_format($total, 0, ',', '.') }}</span>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
