<x-filament-panels::page>
@php
    $gs = fn ($n) => number_format((float) $n, 0, ',', '.');
    $iconos = ['efectivo' => '💵', 'transferencia' => '🏦', 'tarjeta' => '💳', 'otros' => '🧾'];
    $nombres = ['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'tarjeta' => 'Tarjeta', 'otros' => 'Otros'];
@endphp

<style>
    .cc-wrap { display: grid; gap: 1.1rem; }
    .cc-top { display: flex; gap: .8rem; flex-wrap: wrap; }
    .cc-stat { flex: 1 1 11rem; background: #fff; border: 1px solid #e6ebf2; border-radius: 1.2rem; padding: .85rem 1.1rem; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
    .cc-stat small { display: block; font-size: .68rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #8b97ad; }
    .cc-stat b { display: block; font-size: 1.45rem; font-weight: 800; letter-spacing: -.03em; color: #0b1b3a; line-height: 1.2; margin-top: .15rem; }
    .cc-stat.azul { background: linear-gradient(135deg, #0047AB, #0a6cdc); border: 0; }
    .cc-stat.azul small { color: rgba(255,255,255,.75); } .cc-stat.azul b { color: #fff; }
    .cc-barra { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; }
    .cc-buscar { flex: 1 1 15rem; display: flex; align-items: center; gap: .5rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 999px; padding: .15rem 1rem; }
    .cc-buscar:focus-within { border-color: #0047AB; box-shadow: 0 0 0 4px rgba(0,71,171,.12); }
    .cc-buscar input { flex: 1; border: 0; outline: 0; background: transparent; padding: .6rem 0; font-size: .92rem; min-width: 0; }
    .cc-buscar svg { width: 1.1rem; height: 1.1rem; color: #94a3b8; flex: 0 0 auto; }
    .cc-sel { border: 1px solid #e2e8f0; background: #fff; border-radius: 999px; padding: .6rem 1rem; font-size: .88rem; font-weight: 600; }
    .cc-lista { display: grid; gap: .65rem; }
    .cc-fila { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(0, 1.3fr) auto auto; gap: 1rem; align-items: center; background: #fff; border: 1px solid #e6ebf2; border-radius: 1.1rem; padding: .85rem 1.1rem; box-shadow: 0 1px 2px rgba(15,23,42,.03); }
    .cc-fila:hover { border-color: #c7d6ef; }
    .cc-fila .cliente { font-weight: 800; color: #0b1b3a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cc-fila .sub { font-size: .76rem; color: #64748b; margin-top: .15rem; }
    .cc-fila .num { font-size: .68rem; font-weight: 800; letter-spacing: .08em; color: #8b97ad; }
    .cc-pagos { display: flex; gap: .35rem; flex-wrap: wrap; }
    .cc-pago { font-size: .74rem; font-weight: 700; background: #eef3fc; color: #0b3a82; border-radius: 999px; padding: .2rem .65rem; white-space: nowrap; }
    .cc-total { font-size: 1.25rem; font-weight: 800; letter-spacing: -.02em; color: #0b1b3a; text-align: right; white-space: nowrap; }
    .cc-reimp { display: inline-flex; align-items: center; gap: .4rem; background: #fff; color: #0047AB; border: 1.5px solid #c7d6ef; border-radius: .8rem; padding: .55rem .9rem; font-weight: 800; font-size: .85rem; text-decoration: none; white-space: nowrap; }
    .cc-reimp:hover { background: #0047AB; color: #fff; border-color: #0047AB; }
    .cc-vacio { text-align: center; padding: 3rem 1rem; background: #fff; border: 1px dashed #cbd5e1; border-radius: 1.4rem; color: #64748b; }
    .cc-vacio b { display: block; font-size: 1.1rem; color: #0b1b3a; margin: .4rem 0 .2rem; }
    .cc-mas { text-align: center; font-size: .8rem; color: #92400e; background: #fffbeb; border-radius: .8rem; padding: .5rem; }
    @media (max-width: 760px) {
        .cc-fila { grid-template-columns: 1fr auto; }
        .cc-fila .pagos { grid-column: 1 / -1; order: 3; }
        .cc-fila .acc { grid-column: 1 / -1; order: 4; }
        .cc-reimp { width: 100%; justify-content: center; }
    }
</style>

<div class="cc-wrap">
    <div class="cc-top">
        <div class="cc-stat azul"><small>Total cobrado</small><b>Gs. {{ $gs($d['total']) }}</b></div>
        <div class="cc-stat"><small>Cobros</small><b>{{ $d['cantidad'] }}</b></div>
        <div class="cc-stat"><small>💵 Efectivo</small><b>Gs. {{ $gs($d['por_forma']['efectivo']) }}</b></div>
        <div class="cc-stat"><small>🏦 Transferencia</small><b>Gs. {{ $gs($d['por_forma']['transferencia']) }}</b></div>
        <div class="cc-stat"><small>💳 Tarjeta</small><b>Gs. {{ $gs($d['por_forma']['tarjeta']) }}</b></div>
    </div>

    <div class="cc-barra">
        <label class="cc-buscar">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar por cliente, vendedor, N.° de venta o de cobro" aria-label="Buscar cobro">
        </label>
        <select wire:model.live="periodo" class="cc-sel" aria-label="Período">
            @foreach($periodos as $k => $etiqueta)<option value="{{ $k }}">{{ $etiqueta }}</option>@endforeach
        </select>
        <select wire:model.live="forma" class="cc-sel" aria-label="Forma de pago">
            <option value="">Todas las formas de pago</option>
            @foreach($formas as $k => $etiqueta)<option value="{{ $k }}">{{ $etiqueta }}</option>@endforeach
        </select>
    </div>

    @if(count($d['cobros']))
        <div class="cc-lista">
            @foreach($d['cobros'] as $c)
                <div class="cc-fila" wire:key="cobro-{{ $c['cajanr'] }}">
                    <div>
                        <div class="num">COBRO {{ $c['cajanr'] }} · VENTA N.° {{ $c['numero'] }}</div>
                        <div class="cliente" title="{{ $c['cliente'] }}">{{ $c['cliente'] }}</div>
                        <div class="sub">{{ $c['fecha'] }}@if($c['hora'] !== '') · {{ $c['hora'] }}@endif · Vendedor: {{ $c['vendedor'] }}</div>
                    </div>
                    <div class="cc-pagos pagos">
                        @foreach($c['pagos'] as $forma => $monto)
                            <span class="cc-pago">{{ $iconos[$forma] }} {{ $nombres[$forma] }} {{ $gs($monto) }}</span>
                        @endforeach
                    </div>
                    <div class="cc-total">Gs. {{ $gs($c['total']) }}</div>
                    <div class="acc">
                        <a class="cc-reimp" href="{{ \App\Filament\Pages\ConsultaCobrosPage::urlTicket($c['invnr']) }}" target="_blank" rel="noopener">
                            <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
                            Reimprimir
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        @if($d['hay_mas'])
            <div class="cc-mas">Se muestran los 100 cobros más recientes de {{ $d['cantidad'] }}. Usá la búsqueda o un período más corto para encontrar uno anterior.</div>
        @endif
    @else
        <div class="cc-vacio">
            <div style="font-size:2.2rem;">🧾</div>
            <b>No hay cobros para mostrar</b>
            <span>Probá con otro período o cambiá la búsqueda.</span>
        </div>
    @endif
</div>
</x-filament-panels::page>
