<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Mi meta</x-slot>
        <x-slot name="description">{{ $meta['mes'] ?? $mes }}</x-slot>

        @if($meta)
            @php
                $pct    = $meta['pct'];
                $barra  = min(100, $pct);
                $color  = $pct >= 100 ? '#10b981' : ($pct >= 60 ? '#0047AB' : ($pct >= 30 ? '#f59e0b' : '#ef4444'));
                $gs     = fn ($n) => 'Gs. ' . number_format($n, 0, ',', '.');
                $radio  = 52;
                $largo  = 2 * M_PI * $radio;
            @endphp
            <div style="display:flex;align-items:center;gap:1.4rem;flex-wrap:wrap;">
                {{-- Anillo de avance --}}
                <div style="position:relative;width:9.5rem;height:9.5rem;flex:0 0 auto;">
                    <svg viewBox="0 0 120 120" style="width:100%;height:100%;transform:rotate(-90deg);">
                        <circle cx="60" cy="60" r="{{ $radio }}" fill="none" stroke="rgba(148,163,184,.22)" stroke-width="11"/>
                        <circle cx="60" cy="60" r="{{ $radio }}" fill="none" stroke="{{ $color }}" stroke-width="11" stroke-linecap="round"
                                stroke-dasharray="{{ $largo }}" stroke-dashoffset="{{ $largo * (1 - $barra / 100) }}"/>
                    </svg>
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                        <span style="font-size:1.9rem;font-weight:800;letter-spacing:-.03em;line-height:1;">{{ number_format($pct, 0, ',', '.') }}%</span>
                        <span style="font-size:.7rem;opacity:.55;margin-top:.2rem;">de la meta</span>
                    </div>
                </div>

                <div style="flex:1 1 11rem;min-width:0;display:grid;gap:.7rem;">
                    <div>
                        <div style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;opacity:.5;">Vendido</div>
                        <div style="font-size:1.25rem;font-weight:800;letter-spacing:-.02em;">{{ $gs($meta['vendido']) }}</div>
                    </div>
                    <div style="display:flex;gap:1.4rem;flex-wrap:wrap;">
                        <div>
                            <div style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;opacity:.5;">Meta</div>
                            <div style="font-size:.95rem;font-weight:700;">{{ $gs($meta['meta']) }}</div>
                        </div>
                        <div>
                            <div style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;opacity:.5;">Falta</div>
                            <div style="font-size:.95rem;font-weight:700;color:{{ $meta['faltante'] > 0 ? '#ef4444' : '#10b981' }};">{{ $meta['faltante'] > 0 ? $gs($meta['faltante']) : 'Cumplida' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top:1rem;padding:.7rem .9rem;border-radius:.8rem;background:rgba(0,71,171,.07);font-size:.85rem;">
                @if($pct >= 100)
                    🎉 <strong>¡Meta cumplida!</strong> Superaste la meta del mes.
                @elseif($meta['dias_restantes'] > 0)
                    Para llegar a la meta necesitás vender unos <strong>{{ $gs($meta['ritmo']) }} por día</strong> durante los {{ $meta['dias_restantes'] }} {{ $meta['dias_restantes'] === 1 ? 'día' : 'días' }} que quedan del mes.
                @else
                    El mes terminó y faltaron <strong>{{ $gs($meta['faltante']) }}</strong> para la meta.
                @endif
            </div>
        @else
            <div style="display:flex;flex-direction:column;align-items:center;text-align:center;padding:1.6rem .5rem;gap:.5rem;">
                <div style="width:3.2rem;height:3.2rem;border-radius:999px;background:rgba(0,71,171,.1);display:grid;place-items:center;font-size:1.5rem;">🎯</div>
                <div style="font-weight:700;">Todavía no tenés una meta para {{ $mes }}</div>
                <div style="font-size:.85rem;opacity:.6;max-width:22rem;">Pedile a tu administrador que te cargue una en <em>Metas de Ventas</em> y acá vas a ver tu avance.</div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
