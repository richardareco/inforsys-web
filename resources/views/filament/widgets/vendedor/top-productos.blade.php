<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Mis productos más vendidos</x-slot>
        <x-slot name="description">{{ $etiqueta }}</x-slot>

        @if(count($productos))
            <div style="display:grid;gap:.5rem;">
                @foreach($productos as $i => $p)
                    @php
                        $ancho  = $maximo > 0 ? max(4, $p['unidades'] / $maximo * 100) : 0;
                        $medalla = [0 => '#f59e0b', 1 => '#94a3b8', 2 => '#b45309'][$i] ?? '#0047AB';
                        $uni = rtrim(rtrim(number_format($p['unidades'], 2, ',', '.'), '0'), ',');
                    @endphp
                    <div style="display:flex;align-items:center;gap:.75rem;padding:.55rem .6rem;border-radius:.8rem;background:rgba(148,163,184,.08);">
                        <span style="flex:0 0 auto;width:1.6rem;height:1.6rem;border-radius:999px;background:{{ $medalla }};color:#fff;font-size:.72rem;font-weight:800;display:grid;place-items:center;">{{ $i + 1 }}</span>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:.86rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $p['nombre'] }}">{{ $p['nombre'] }}</div>
                            <div style="height:.32rem;border-radius:999px;background:rgba(148,163,184,.2);margin-top:.35rem;overflow:hidden;">
                                <div style="height:100%;width:{{ $ancho }}%;background:#0047AB;border-radius:999px;"></div>
                            </div>
                        </div>
                        <div style="flex:0 0 auto;text-align:right;line-height:1.25;">
                            <div style="font-size:.9rem;font-weight:800;">{{ $uni }} <span style="font-size:.7rem;font-weight:600;opacity:.55;">u.</span></div>
                            <div style="font-size:.72rem;opacity:.6;">Gs. {{ number_format($p['monto'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align:center;padding:2rem .5rem;opacity:.55;font-size:.9rem;">No hay ventas tuyas en este período.</div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
