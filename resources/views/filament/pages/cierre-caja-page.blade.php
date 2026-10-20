@php
    $fmt = fn ($n) => 'Gs. ' . number_format((float) $n, 0, ',', '.');
    $cuadra = abs($diferencia) < 1;
@endphp

<x-filament-panels::page>
<style>
    .cc-wrap { display: grid; gap: 1.25rem; }
    .cc-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; }
    .cc-fecha { font-size: 1.05rem; font-weight: 800; color: #0b2a5b; }
    .dark .cc-fecha { color: #e2e8f0; }
    .cc-ctrl { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
    .cc-btn {
        padding: .5rem .9rem; border-radius: .75rem; border: 1px solid rgba(148,163,184,.4);
        background: #fff; font-weight: 700; font-size: .8rem; color: #334155; cursor: pointer;
        transition: background .15s ease, transform .15s ease;
    }
    .cc-btn:hover { background: #f1f5f9; transform: translateY(-1px); }
    .cc-btn.on { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; border-color: transparent; }
    .cc-input {
        padding: .5rem .75rem; border-radius: .75rem; border: 1px solid rgba(148,163,184,.5);
        background: #fff; font-size: .85rem; font-weight: 600; color: #0f172a;
    }
    .cc-print { background: #0f172a; color: #fff; border-color: transparent; }
    .cc-print:hover { background: #1e293b; }

    .cc-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); }
    .cc-card {
        border-radius: 1.1rem; padding: 1.1rem 1.2rem; background: #fff;
        border: 1px solid rgba(148,163,184,.25);
        box-shadow: 0 12px 28px -20px rgba(15,23,42,.35);
    }
    .dark .cc-card { background: #18181b; border-color: rgba(255,255,255,.08); }
    .cc-label { font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; }
    .cc-value { margin-top: .35rem; font-size: 1.6rem; font-weight: 900; letter-spacing: -.02em; color: #0f172a; }
    .dark .cc-value { color: #f8fafc; }
    .cc-sub { margin-top: .15rem; font-size: .78rem; color: #64748b; }

    .cc-hero {
        border-radius: 1.2rem; padding: 1.2rem 1.4rem; color: #fff;
        background: linear-gradient(135deg, #0a3fa8, #0047ab 45%, #0284c7);
        box-shadow: 0 18px 34px -18px rgba(10,63,168,.7);
    }
    .cc-hero .cc-label { color: rgba(255,255,255,.75); }
    .cc-hero .cc-value { color: #fff; font-size: 2rem; }
    .cc-hero .cc-sub { color: rgba(255,255,255,.8); }

    .cc-section-title { font-size: .95rem; font-weight: 800; color: #0f172a; margin-bottom: .8rem; }
    .dark .cc-section-title { color: #f1f5f9; }

    .cc-metodo { display: grid; grid-template-columns: 1fr auto; gap: .35rem .75rem; align-items: center; padding: .65rem 0; border-bottom: 1px solid rgba(148,163,184,.18); }
    .cc-metodo:last-child { border-bottom: none; }
    .cc-metodo-name { font-weight: 700; font-size: .9rem; display: flex; align-items: center; gap: .55rem; }
    .cc-dot { width: .7rem; height: .7rem; border-radius: 9999px; display: inline-block; }
    .cc-metodo-monto { font-weight: 800; font-size: .95rem; text-align: right; }
    .cc-bar-bg { grid-column: 1 / -1; height: .45rem; border-radius: 9999px; background: rgba(148,163,184,.2); overflow: hidden; }
    .cc-bar-fill { height: 100%; border-radius: 9999px; }

    .cc-cmp { display: grid; gap: .5rem; }
    .cc-row { display: flex; justify-content: space-between; gap: 1rem; font-size: .9rem; }
    .cc-row b { font-weight: 800; }
    .cc-row.total { padding-top: .6rem; margin-top: .25rem; border-top: 1px dashed rgba(148,163,184,.5); font-size: 1rem; }
    .cc-badge {
        display: inline-flex; align-items: center; gap: .4rem; padding: .35rem .8rem; border-radius: 9999px;
        font-weight: 800; font-size: .78rem; margin-top: .4rem; width: fit-content;
    }
    .cc-ok { background: #dcfce7; color: #15803d; }
    .cc-warn { background: #fef3c7; color: #b45309; }
    .cc-bad { background: #fee2e2; color: #b91c1c; }

    .cc-table-wrap { overflow-x: auto; }
    .cc-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
    .cc-table th { text-align: left; font-size: .68rem; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; padding: .5rem .6rem; border-bottom: 1px solid rgba(148,163,184,.3); }
    .cc-table td { padding: .55rem .6rem; border-bottom: 1px solid rgba(148,163,184,.14); }
    .cc-table tr:hover td { background: rgba(99,102,241,.05); }
    .cc-table .r { text-align: right; }
    .cc-pill { display: inline-block; padding: .15rem .55rem; border-radius: 9999px; background: #eef2ff; color: #4338ca; font-weight: 700; font-size: .72rem; }
    .cc-pill.red { background: #fee2e2; color: #b91c1c; }
    .cc-empty { padding: 2rem; text-align: center; color: #94a3b8; }

    @media print {
        .fi-sidebar, .fi-topbar, .cc-ctrl, .fi-header, .app-fullscreen-btn { display: none !important; }
        .fi-main-ctn, .fi-main { margin: 0 !important; padding: 0 !important; max-width: 100% !important; }
        .cc-card, .cc-hero { box-shadow: none !important; break-inside: avoid; }
    }
</style>

<div class="cc-wrap">

    {{-- Barra: fecha + controles --}}
    <div class="cc-bar">
        <div class="cc-fecha">{{ $fechaTexto }}</div>
        <div class="cc-ctrl">
            <button type="button" wire:click="irHoy" class="cc-btn {{ $esHoy ? 'on' : '' }}">Hoy</button>
            <button type="button" wire:click="irAyer" class="cc-btn {{ $fecha === today()->subDay()->toDateString() ? 'on' : '' }}">Ayer</button>
            <input type="date" wire:model.live="fecha" max="{{ today()->toDateString() }}" class="cc-input">
            <a href="{{ url('/cierre-caja/imprimir') }}?fecha={{ $fecha }}" target="_blank" rel="noopener" class="cc-btn cc-print" style="text-decoration:none;display:inline-block;">Imprimir informe</a>
        </div>
    </div>

    {{-- Resumen principal --}}
    <div class="cc-grid">
        <div class="cc-hero">
            <div class="cc-label">Total cobrado en caja</div>
            <div class="cc-value">{{ $fmt($totalCobrado) }}</div>
            <div class="cc-sub">{{ $movimientos }} {{ $movimientos === 1 ? 'movimiento' : 'movimientos' }} de caja</div>
        </div>
        <div class="cc-card">
            <div class="cc-label">Ventas del día</div>
            <div class="cc-value">{{ $fmt($ventasTotal) }}</div>
            <div class="cc-sub">{{ $ventasCantidad }} {{ $ventasCantidad === 1 ? 'venta' : 'ventas' }}</div>
        </div>
        <div class="cc-card">
            <div class="cc-label">Ventas al contado</div>
            <div class="cc-value">{{ $fmt($ventasContado) }}</div>
            <div class="cc-sub">Deben estar cobradas en caja</div>
        </div>
        <div class="cc-card">
            <div class="cc-label">Ventas a crédito</div>
            <div class="cc-value">{{ $fmt($ventasCredito) }}</div>
            <div class="cc-sub">No ingresan a caja</div>
        </div>
    </div>

    <div class="cc-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
        {{-- Por método de pago --}}
        <div class="cc-card">
            <div class="cc-section-title">Cobros por forma de pago</div>
            @foreach($metodos as $m)
                <div class="cc-metodo">
                    <div class="cc-metodo-name"><span class="cc-dot" style="background:{{ $m['color'] }};"></span>{{ $m['label'] }}</div>
                    <div class="cc-metodo-monto">{{ $fmt($m['monto']) }}</div>
                    <div class="cc-bar-bg"><div class="cc-bar-fill" style="width:{{ number_format($m['pct'], 1, '.', '') }}%;background:{{ $m['color'] }};"></div></div>
                </div>
            @endforeach
        </div>

        {{-- Comparación contra ventas --}}
        <div class="cc-card">
            <div class="cc-section-title">Caja contra ventas</div>
            <div class="cc-cmp">
                <div class="cc-row"><span>Ventas al contado</span><b>{{ $fmt($ventasContado) }}</b></div>
                <div class="cc-row"><span>Cobrado en caja</span><b>{{ $fmt($totalCobrado) }}</b></div>
                <div class="cc-row total">
                    <span>Diferencia</span>
                    <b style="color: {{ $cuadra ? '#15803d' : '#b91c1c' }};">{{ $diferencia > 0 ? '+' : '' }}{{ $fmt($diferencia) }}</b>
                </div>
                @if($cuadra)
                    <span class="cc-badge cc-ok">Caja cuadrada</span>
                @elseif($diferencia > 0)
                    <span class="cc-badge cc-bad">Faltan {{ $fmt($diferencia) }} por registrar en caja</span>
                @else
                    <span class="cc-badge cc-warn">La caja supera a las ventas en {{ $fmt(abs($diferencia)) }}</span>
                @endif
                @if($sinCobro > 0)
                    <span class="cc-badge cc-bad">{{ $sinCobro }} {{ $sinCobro === 1 ? 'venta sin cobro' : 'ventas sin cobro' }} en caja</span>
                @endif
                @if($salidas > 0)
                    <div class="cc-row" style="margin-top:.5rem;color:#64748b;"><span>Devoluciones / salidas (no incluidas)</span><b>{{ $fmt($salidas) }}</b></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Detalle --}}
    <div class="cc-card">
        <div class="cc-section-title">Detalle de ventas del día</div>
        <div class="cc-table-wrap">
            <table class="cc-table">
                <thead>
                    <tr>
                        <th>Nro. venta</th>
                        <th>Hora</th>
                        <th>Cliente</th>
                        <th>Forma de pago</th>
                        <th class="r">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detalle as $d)
                        <tr>
                            <td><b>{{ $d->invnr }}</b></td>
                            <td>{{ $d->hora ? substr((string) $d->hora, 0, 5) : '—' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($d->custname ?? '—', 32) }}</td>
                            <td><span class="cc-pill {{ $d->metodo === 'Sin cobro en caja' ? 'red' : '' }}">{{ $d->metodo }}</span></td>
                            <td class="r"><b>{{ $fmt($d->total) }}</b></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="cc-empty">No hay ventas registradas en esta fecha.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
</x-filament-panels::page>
