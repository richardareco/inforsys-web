<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Ventas por categoría</x-slot>
        <x-slot name="description">{{ $etiqueta }}</x-slot>

        @if(count($categorias))
            {{-- Barra apilada con el peso de cada categoría --}}
            <div style="display:flex;height:.7rem;border-radius:999px;overflow:hidden;background:rgba(148,163,184,.2);margin-bottom:1rem;">
                @foreach($categorias as $c)
                    <div title="{{ $c['nombre'] }}: {{ number_format($c['pct'], 1, ',', '.') }}%" style="width:{{ max(0.5, $c['pct']) }}%;background:{{ $c['color'] }};"></div>
                @endforeach
            </div>

            <div style="display:grid;gap:.85rem;">
                @foreach($categorias as $c)
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:baseline;gap:.6rem;font-size:.85rem;">
                            <span style="display:flex;align-items:center;gap:.5rem;min-width:0;">
                                <i style="width:.65rem;height:.65rem;border-radius:999px;background:{{ $c['color'] }};flex:0 0 auto;display:block;"></i>
                                <span style="font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $c['nombre'] }}</span>
                            </span>
                            <span style="white-space:nowrap;"><strong>Gs. {{ number_format($c['monto'], 0, ',', '.') }}</strong> <span style="opacity:.55;">· {{ number_format($c['pct'], 1, ',', '.') }}%</span></span>
                        </div>
                        <div style="height:.4rem;border-radius:999px;background:rgba(148,163,184,.18);margin-top:.35rem;overflow:hidden;">
                            <div style="height:100%;width:{{ $c['pct'] }}%;background:{{ $c['color'] }};border-radius:999px;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align:center;padding:2rem .5rem;opacity:.55;font-size:.9rem;">No hay ventas tuyas en este período.</div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
