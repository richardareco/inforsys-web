<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Metas por vendedor</x-slot>
        <x-slot name="description">{{ $d['mes'] }} · {{ $d['cerrado'] ? 'mes cerrado' : 'a esta altura del mes se espera ' . number_format($d['esperado'], 0, ',', '.') . '%' }}</x-slot>

        @php
            $gs = fn ($n) => 'Gs. ' . number_format($n, 0, ',', '.');
            $estados = [
                'cumplida' => ['Cumplida', '#10b981'],
                'al_dia'   => ['Al día', '#0047AB'],
                'atrasada' => [$d['cerrado'] ? 'No alcanzó' : 'Atrasada', '#ef4444'],
            ];
        @endphp

        @if(count($d['vendedores']))
            <div style="display:grid;gap:.7rem;">
                @foreach($d['vendedores'] as $i => $v)
                    @php
                        [$etiqueta, $color] = $estados[$v['estado']];
                        $barra = min(100, $v['pct']);
                    @endphp
                    <div style="padding:.85rem 1rem;border-radius:1rem;background:rgba(148,163,184,.08);border:1px solid rgba(148,163,184,.14);">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.8rem;flex-wrap:wrap;">
                            <div style="display:flex;align-items:center;gap:.7rem;min-width:0;">
                                <span style="flex:0 0 auto;width:2rem;height:2rem;border-radius:999px;background:{{ $color }};color:#fff;font-size:.75rem;font-weight:800;display:grid;place-items:center;">{{ $i + 1 }}</span>
                                <div style="min-width:0;">
                                    <div style="font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $v['nombre'] }}</div>
                                    <div style="font-size:.78rem;opacity:.6;">{{ $gs($v['vendido']) }} de {{ $gs($v['meta']) }}</div>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:.7rem;">
                                <span style="font-size:.7rem;font-weight:800;padding:.2rem .65rem;border-radius:999px;color:{{ $color }};background:{{ $color }}1f;">{{ $etiqueta }}</span>
                                <span style="font-size:1.35rem;font-weight:800;letter-spacing:-.02em;color:{{ $color }};min-width:4.6rem;text-align:right;">{{ number_format($v['pct'], 1, ',', '.') }}%</span>
                            </div>
                        </div>

                        {{-- Barra de avance con la marca de lo que se espera a esta altura del mes --}}
                        <div style="position:relative;height:.6rem;border-radius:999px;background:rgba(148,163,184,.22);margin-top:.7rem;overflow:visible;">
                            <div style="height:100%;width:{{ $barra }}%;background:{{ $color }};border-radius:999px;"></div>
                            @if(!$d['cerrado'])
                                <i title="Ritmo esperado: {{ number_format($d['esperado'], 0, ',', '.') }}%" style="position:absolute;top:-.2rem;left:{{ min(100, $d['esperado']) }}%;width:2px;height:1rem;background:#64748b;opacity:.7;"></i>
                            @endif
                        </div>

                        <div style="font-size:.76rem;opacity:.65;margin-top:.45rem;">
                            @if($v['faltante'] > 0) Faltan <strong>{{ $gs($v['faltante']) }}</strong> para la meta. @else Superó la meta por <strong>{{ $gs($v['vendido'] - $v['meta']) }}</strong>. @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align:center;padding:2rem .5rem;display:grid;gap:.4rem;">
                <div style="font-size:1.6rem;">🎯</div>
                <div style="font-weight:700;">No hay metas por vendedor para {{ $d['mes'] }}</div>
                <div style="font-size:.85rem;opacity:.6;">Cargalas en <em>Metas de Ventas</em> eligiendo "Por vendedor".</div>
            </div>
        @endif

        @if(count($d['sin_meta']))
            <div style="margin-top:1rem;padding:.7rem .9rem;border-radius:.8rem;background:rgba(245,158,11,.1);font-size:.82rem;">
                Vendieron en el mes pero <strong>no tienen meta</strong>: {{ implode(', ', $d['sin_meta']) }}.
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
