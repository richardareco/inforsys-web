@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
    $gs  = fn ($n) => 'Gs. ' . number_format((float) $n, 0, ',', '.');
    $pct = fn ($parte, $total) => $total > 0 ? $parte / $total * 100 : 0;
    $pf  = fn ($n) => number_format((float) $n, 1, ',', '.') . '%';
    $cambio = function ($c) {
        if ($c === null) return null;
        return ['txt' => ($c >= 0 ? '+' : '') . number_format($c, 1, ',', '.') . '%', 'up' => $c >= 0];
    };
    $vistas = \App\Filament\Pages\Informes\InformeVentasPage::VISTAS;
    $acento = '#16a34a';
@endphp

<x-filament-panels::page>
<style>
    .iv { display: grid; gap: 1.1rem; }
    .iv-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .8rem; }
    .iv-title { display: flex; align-items: center; gap: .8rem; }
    .iv-ico { width: 2.9rem; height: 2.9rem; border-radius: .95rem; background: linear-gradient(135deg, #16a34a, #15803d); color: #fff; display: grid; place-items: center; box-shadow: 0 10px 20px -10px #16a34a; }
    .iv-ico svg { width: 1.5rem; height: 1.5rem; }
    .iv-h1 { font-size: 1.45rem; font-weight: 900; letter-spacing: -.02em; color: #0f172a; line-height: 1.1; }
    .dark .iv-h1 { color: #f1f5f9; }
    .iv-rango { font-size: .82rem; color: #64748b; margin-top: .15rem; }

    .iv-ctrl { display: flex; flex-wrap: wrap; align-items: center; gap: .45rem; }
    .iv-btn { padding: .45rem .85rem; border-radius: .7rem; border: 1px solid rgba(148,163,184,.4); background: #fff; font-weight: 700; font-size: .78rem; color: #334155; cursor: pointer; transition: background .15s ease, transform .15s ease; }
    .iv-btn:hover { background: #f1f5f9; transform: translateY(-1px); }
    .iv-btn.print { background: #0f172a; color: #fff; border-color: transparent; }
    .iv-date { padding: .42rem .65rem; border-radius: .7rem; border: 1px solid rgba(148,163,184,.5); background: #fff; font-size: .8rem; font-weight: 600; color: #0f172a; }
    .iv-sep { font-size: .75rem; color: #94a3b8; }

    .iv-kpis { display: grid; gap: .85rem; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); }
    .iv-kpi { border-radius: 1.1rem; padding: 1rem 1.15rem; background: #fff; border: 1px solid rgba(148,163,184,.25); box-shadow: 0 12px 26px -22px rgba(15,23,42,.4); border-top: 3px solid var(--c); }
    .dark .iv-kpi { background: #18181b; border-color: rgba(255,255,255,.08); border-top-color: var(--c); }
    .iv-kpi-l { font-size: .64rem; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; color: #94a3b8; }
    .iv-kpi-v { margin-top: .3rem; font-size: 1.45rem; font-weight: 900; letter-spacing: -.02em; color: #0f172a; }
    .dark .iv-kpi-v { color: #f8fafc; }
    .iv-chg { display: inline-flex; align-items: center; gap: .2rem; margin-top: .3rem; padding: .12rem .55rem; border-radius: 9999px; font-size: .68rem; font-weight: 800; }
    .iv-chg.up { background: #dcfce7; color: #15803d; }
    .iv-chg.down { background: #fee2e2; color: #b91c1c; }
    .iv-chg-n { font-size: .7rem; color: #94a3b8; margin-top: .3rem; }

    .iv-tabs { display: flex; flex-wrap: wrap; gap: .4rem; padding: .35rem; border-radius: 1rem; background: rgba(148,163,184,.14); width: fit-content; max-width: 100%; }
    .iv-tab { padding: .5rem .95rem; border-radius: .75rem; border: none; background: transparent; font-weight: 700; font-size: .82rem; color: #475569; cursor: pointer; transition: background .15s ease, color .15s ease; }
    .iv-tab:hover { background: rgba(255,255,255,.7); }
    .iv-tab.on { background: linear-gradient(135deg, #16a34a, #15803d); color: #fff; box-shadow: 0 8px 16px -8px #16a34a; }

    .iv-card { border-radius: 1.2rem; padding: 1.2rem 1.4rem; background: #fff; border: 1px solid rgba(148,163,184,.25); box-shadow: 0 12px 28px -22px rgba(15,23,42,.4); }
    .dark .iv-card { background: #18181b; border-color: rgba(255,255,255,.08); }
    .iv-card-t { font-size: .95rem; font-weight: 800; color: #0f172a; margin-bottom: .9rem; }
    .dark .iv-card-t { color: #f1f5f9; }
    .iv-grid2 { display: grid; gap: 1.1rem; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); }

    .iv-wrap { overflow-x: auto; }
    .iv-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
    .iv-table th { text-align: left; font-size: .66rem; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; padding: .5rem .6rem; border-bottom: 1px solid rgba(148,163,184,.3); white-space: nowrap; }
    .iv-table td { padding: .6rem .6rem; border-bottom: 1px solid rgba(148,163,184,.14); vertical-align: middle; }
    .iv-table tbody tr:hover td { background: rgba(22,163,74,.05); }
    .iv-table tfoot td { font-weight: 800; border-top: 2px solid rgba(148,163,184,.4); border-bottom: none; padding-top: .7rem; }
    .iv-table .r { text-align: right; white-space: nowrap; }
    .iv-bar { height: .42rem; border-radius: 9999px; background: rgba(148,163,184,.2); overflow: hidden; min-width: 90px; }
    .iv-bar > i { display: block; height: 100%; border-radius: 9999px; background: linear-gradient(90deg, #22c55e, #15803d); }
    .iv-num { font-weight: 800; color: #94a3b8; width: 2rem; }
    .iv-empty { padding: 2.2rem; text-align: center; color: #94a3b8; font-size: .9rem; }
    .iv-note { margin-top: .8rem; font-size: .74rem; color: #94a3b8; }

    /* Gráfico de columnas */
    .iv-chart { display: flex; align-items: flex-end; gap: 3px; height: 200px; padding-top: 1rem; overflow-x: auto; }
    .iv-col { flex: 1 0 14px; min-width: 14px; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; height: 100%; }
    .iv-col-bar { width: 100%; max-width: 34px; border-radius: 6px 6px 0 0; background: linear-gradient(180deg, #22c55e, #15803d); min-height: 2px; transition: filter .15s ease; }
    .iv-col:hover .iv-col-bar { filter: brightness(1.15); }
    .iv-col-l { margin-top: .35rem; font-size: .6rem; color: #94a3b8; white-space: nowrap; }
    .iv-col-bar.zero { background: rgba(148,163,184,.3); }

    .iv-met { display: grid; grid-template-columns: 1fr auto; gap: .3rem .8rem; align-items: center; padding: .75rem 0; border-bottom: 1px solid rgba(148,163,184,.16); }
    .iv-met:last-child { border-bottom: none; }
    .iv-met-n { display: flex; align-items: center; gap: .6rem; font-weight: 700; font-size: .92rem; }
    .iv-dot { width: .75rem; height: .75rem; border-radius: 9999px; display: inline-block; }
    .iv-met-m { font-weight: 900; text-align: right; }
    .iv-met-bar { grid-column: 1 / -1; height: .5rem; border-radius: 9999px; background: rgba(148,163,184,.2); overflow: hidden; }
    .iv-met-bar > i { display: block; height: 100%; border-radius: 9999px; }
    .iv-met-s { grid-column: 1 / -1; font-size: .72rem; color: #94a3b8; }

    .iv-best { display: inline-flex; align-items: center; gap: .4rem; margin-top: .8rem; padding: .35rem .8rem; border-radius: 9999px; background: #fef3c7; color: #b45309; font-weight: 800; font-size: .76rem; }

    @media print {
        .fi-sidebar, .fi-topbar, .iv-ctrl, .iv-tabs, .fi-header, .app-fullscreen-btn { display: none !important; }
        .fi-main-ctn, .fi-main { margin: 0 !important; padding: 0 !important; max-width: 100% !important; }
        .iv-card, .iv-kpi { box-shadow: none !important; break-inside: avoid; }
    }
</style>

<div class="iv">

    {{-- Título + filtros --}}
    <div class="iv-top">
        <div class="iv-title">
            <div class="iv-ico"><x-heroicon-o-chart-bar /></div>
            <div>
                <div class="iv-h1">Informes de ventas</div>
                <div class="iv-rango">{{ $rangoTexto }} · {{ $dias }} {{ $dias === 1 ? 'día' : 'días' }}</div>
            </div>
        </div>
        <div class="iv-ctrl">
            <button type="button" wire:click="rango('hoy')" class="iv-btn">Hoy</button>
            <button type="button" wire:click="rango('ayer')" class="iv-btn">Ayer</button>
            <button type="button" wire:click="rango('semana')" class="iv-btn">Semana</button>
            <button type="button" wire:click="rango('mes')" class="iv-btn">Mes</button>
            <button type="button" wire:click="rango('año')" class="iv-btn">Año</button>
            <input type="date" wire:model.live="desde" max="{{ $hoy }}" class="iv-date">
            <span class="iv-sep">a</span>
            <input type="date" wire:model.live="hasta" max="{{ $hoy }}" class="iv-date">
            <button type="button" onclick="window.print()" class="iv-btn print">Imprimir</button>
        </div>
    </div>

    {{-- Indicadores --}}
    @php
        $tarjetas = [
            ['l' => 'Total vendido', 'v' => $gs($kpis['total']), 'c' => $cambio($kpis['cambioTotal']), 'color' => '#16a34a'],
            ['l' => 'Cantidad de ventas', 'v' => $fmt($kpis['cantidad']), 'c' => $cambio($kpis['cambioCantidad']), 'color' => '#2563eb'],
            ['l' => 'Ticket promedio', 'v' => $gs($kpis['ticket']), 'c' => $cambio($kpis['cambioTicket']), 'color' => '#d97706'],
            ['l' => 'Unidades vendidas', 'v' => $fmt($kpis['unidades']), 'c' => null, 'color' => '#7c3aed'],
        ];
    @endphp
    <div class="iv-kpis">
        @foreach($tarjetas as $t)
            <div class="iv-kpi" style="--c: {{ $t['color'] }};">
                <div class="iv-kpi-l">{{ $t['l'] }}</div>
                <div class="iv-kpi-v">{{ $t['v'] }}</div>
                @if($t['c'])
                    <span class="iv-chg {{ $t['c']['up'] ? 'up' : 'down' }}">{{ $t['c']['up'] ? '▲' : '▼' }} {{ $t['c']['txt'] }}</span>
                    <div class="iv-chg-n">vs período anterior</div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Pestañas --}}
    <div class="iv-tabs">
        @foreach($vistas as $k => $label)
            <button type="button" wire:click="setVista('{{ $k }}')" class="iv-tab {{ $vista === $k ? 'on' : '' }}">{{ $label }}</button>
        @endforeach
    </div>

    {{-- ══════════ POR PERÍODO ══════════ --}}
    @if($vista === 'periodo')
        <div class="iv-card">
            <div class="iv-card-t">Ventas {{ $datos['porMes'] ? 'por mes' : 'por día' }}</div>
            @if($datos['total'] <= 0)
                <div class="iv-empty">No hay ventas en el período seleccionado.</div>
            @else
                <div class="iv-chart">
                    @foreach($datos['serie'] as $s)
                        @php $h = $datos['max'] > 0 ? max(2, $s['total'] / $datos['max'] * 100) : 2; @endphp
                        <div class="iv-col" title="{{ $s['etiqueta'] }}: {{ $gs($s['total']) }} ({{ $s['cant'] }} ventas)">
                            <div class="iv-col-bar {{ $s['total'] <= 0 ? 'zero' : '' }}" style="height: {{ number_format($h, 1, '.', '') }}%;"></div>
                            <div class="iv-col-l">{{ $s['corta'] }}</div>
                        </div>
                    @endforeach
                </div>
                @if($datos['mejor'])
                    <span class="iv-best">★ Mejor {{ $datos['porMes'] ? 'mes' : 'día' }}: {{ $datos['mejor']['etiqueta'] }} · {{ $gs($datos['mejor']['total']) }}</span>
                @endif
            @endif
        </div>

        @if($datos['total'] > 0)
        <div class="iv-card">
            <div class="iv-card-t">Detalle</div>
            <div class="iv-wrap">
                <table class="iv-table">
                    <thead><tr><th>{{ $datos['porMes'] ? 'Mes' : 'Día' }}</th><th class="r">Ventas</th><th class="r">Total</th><th class="r">Ticket prom.</th><th>Participación</th></tr></thead>
                    <tbody>
                        @foreach(array_reverse($datos['serie']) as $s)
                            @if($s['cant'] > 0)
                            <tr>
                                <td><b>{{ $s['etiqueta'] }}</b></td>
                                <td class="r">{{ $fmt($s['cant']) }}</td>
                                <td class="r"><b>{{ $gs($s['total']) }}</b></td>
                                <td class="r">{{ $gs($s['cant'] > 0 ? $s['total'] / $s['cant'] : 0) }}</td>
                                <td style="min-width:140px;"><div class="iv-bar"><i style="width:{{ number_format($pct($s['total'], $datos['total']), 1, '.', '') }}%;"></i></div></td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot><tr><td>Total</td><td class="r">{{ $fmt($kpis['cantidad']) }}</td><td class="r">{{ $gs($datos['total']) }}</td><td class="r">{{ $gs($kpis['ticket']) }}</td><td></td></tr></tfoot>
                </table>
            </div>
        </div>
        @endif

    {{-- ══════════ VENDEDORES ══════════ --}}
    @elseif($vista === 'vendedores')
        <div class="iv-card">
            <div class="iv-card-t">Ventas por vendedor y comisiones</div>
            @if($datos['filas']->isEmpty())
                <div class="iv-empty">No hay ventas en el período seleccionado.</div>
            @else
            <div class="iv-wrap">
                <table class="iv-table">
                    <thead><tr><th>#</th><th>Vendedor</th><th class="r">Ventas</th><th class="r">Total vendido</th><th class="r">Ticket prom.</th><th>Participación</th><th class="r">Comisión %</th><th class="r">Comisión est.</th></tr></thead>
                    <tbody>
                        @php $totCom = 0; @endphp
                        @foreach($datos['filas'] as $i => $f)
                            @php $com = (float) $f->total * (float) $f->comision / 100; $totCom += $com; @endphp
                            <tr>
                                <td class="iv-num">{{ $i + 1 }}</td>
                                <td><b>{{ $f->nombre }}</b></td>
                                <td class="r">{{ $fmt($f->cant) }}</td>
                                <td class="r"><b>{{ $gs($f->total) }}</b></td>
                                <td class="r">{{ $gs($f->cant > 0 ? $f->total / $f->cant : 0) }}</td>
                                <td style="min-width:130px;"><div class="iv-bar"><i style="width:{{ number_format($pct($f->total, $datos['total']), 1, '.', '') }}%;"></i></div></td>
                                <td class="r">{{ rtrim(rtrim(number_format((float) $f->comision, 2, ',', '.'), '0'), ',') }}%</td>
                                <td class="r"><b>{{ $gs($com) }}</b></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr><td></td><td>Total</td><td class="r">{{ $fmt($datos['filas']->sum('cant')) }}</td><td class="r">{{ $gs($datos['total']) }}</td><td></td><td></td><td></td><td class="r">{{ $gs($totCom) }}</td></tr></tfoot>
                </table>
            </div>
            <p class="iv-note">La comisión es una estimación: total vendido por el porcentaje configurado para cada vendedor.</p>
            @endif
        </div>

    {{-- ══════════ PRODUCTOS ══════════ --}}
    @elseif($vista === 'productos')
        <div class="iv-card">
            <div class="iv-card-t">Productos más vendidos (top 30 por ingresos)</div>
            @if($datos['filas']->isEmpty())
                <div class="iv-empty">No hay ventas en el período seleccionado.</div>
            @else
            <div class="iv-wrap">
                <table class="iv-table">
                    <thead><tr><th>#</th><th>Código</th><th>Producto</th><th class="r">Unidades</th><th class="r">Ventas</th><th class="r">Ingresos</th><th>Participación</th></tr></thead>
                    <tbody>
                        @foreach($datos['filas'] as $i => $f)
                            <tr>
                                <td class="iv-num">{{ $i + 1 }}</td>
                                <td style="color:#64748b;">{{ ltrim((string) $f->item, '0') ?: $f->item }}</td>
                                <td><b>{{ \Illuminate\Support\Str::limit($f->descr, 44) }}</b></td>
                                <td class="r">{{ $fmt($f->qty) }}</td>
                                <td class="r">{{ $fmt($f->ventas) }}</td>
                                <td class="r"><b>{{ $gs($f->ingresos) }}</b></td>
                                <td style="min-width:130px;"><div class="iv-bar"><i style="width:{{ number_format($pct($f->ingresos, $datos['total']), 1, '.', '') }}%;"></i></div><span style="font-size:.68rem;color:#94a3b8;">{{ $pf($pct($f->ingresos, $datos['total'])) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr><td colspan="5">Total de todos los productos</td><td class="r">{{ $gs($datos['total']) }}</td><td></td></tr></tfoot>
                </table>
            </div>
            @endif
        </div>

    {{-- ══════════ CATEGORÍAS ══════════ --}}
    @elseif($vista === 'categorias')
        <div class="iv-card">
            <div class="iv-card-t">Ventas por categoría</div>
            @if($datos['filas']->isEmpty())
                <div class="iv-empty">No hay ventas en el período seleccionado.</div>
            @else
            <div class="iv-wrap">
                <table class="iv-table">
                    <thead><tr><th>#</th><th>Categoría</th><th class="r">Unidades</th><th class="r">Ingresos</th><th class="r">%</th><th>Participación</th></tr></thead>
                    <tbody>
                        @foreach($datos['filas'] as $i => $f)
                            @php $nombre = trim((string) ($f->categoria ?? '')) !== '' ? trim($f->categoria) : 'Sin categoría'; @endphp
                            <tr>
                                <td class="iv-num">{{ $i + 1 }}</td>
                                <td><b>{{ $nombre }}</b></td>
                                <td class="r">{{ $fmt($f->qty) }}</td>
                                <td class="r"><b>{{ $gs($f->ingresos) }}</b></td>
                                <td class="r">{{ $pf($pct($f->ingresos, $datos['total'])) }}</td>
                                <td style="min-width:150px;"><div class="iv-bar"><i style="width:{{ number_format($pct($f->ingresos, $datos['total']), 1, '.', '') }}%;"></i></div></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr><td></td><td>Total</td><td class="r">{{ $fmt($datos['filas']->sum('qty')) }}</td><td class="r">{{ $gs($datos['total']) }}</td><td></td><td></td></tr></tfoot>
                </table>
            </div>
            @endif
        </div>

    {{-- ══════════ CLIENTES ══════════ --}}
    @elseif($vista === 'clientes')
        <div class="iv-card">
            <div class="iv-card-t">Ventas por cliente (top 30)</div>
            @if($datos['filas']->isEmpty())
                <div class="iv-empty">No hay ventas en el período seleccionado.</div>
            @else
            <div class="iv-wrap">
                <table class="iv-table">
                    <thead><tr><th>#</th><th>Cliente</th><th class="r">Ventas</th><th class="r">Total comprado</th><th class="r">Ticket prom.</th><th class="r">Última compra</th><th>Participación</th></tr></thead>
                    <tbody>
                        @foreach($datos['filas'] as $i => $f)
                            <tr>
                                <td class="iv-num">{{ $i + 1 }}</td>
                                <td><b>{{ \Illuminate\Support\Str::limit($f->nombre, 38) }}</b></td>
                                <td class="r">{{ $fmt($f->cant) }}</td>
                                <td class="r"><b>{{ $gs($f->total) }}</b></td>
                                <td class="r">{{ $gs($f->cant > 0 ? $f->total / $f->cant : 0) }}</td>
                                <td class="r">{{ \Carbon\Carbon::parse($f->ultima)->format('d/m/Y') }}</td>
                                <td style="min-width:130px;"><div class="iv-bar"><i style="width:{{ number_format($pct($f->total, $datos['total']), 1, '.', '') }}%;"></i></div></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr><td colspan="3">Total de todos los clientes</td><td class="r">{{ $gs($datos['total']) }}</td><td colspan="3"></td></tr></tfoot>
                </table>
            </div>
            @endif
        </div>

    {{-- ══════════ FORMAS DE PAGO ══════════ --}}
    @elseif($vista === 'pagos')
        <div class="iv-card">
            <div class="iv-card-t">Ventas por forma de pago</div>
            @if($datos['total'] <= 0)
                <div class="iv-empty">No hay movimientos de caja en el período seleccionado.</div>
            @else
                @foreach($datos['metodos'] as $m)
                    @php $p = $pct($m['monto'], $datos['total']); @endphp
                    <div class="iv-met">
                        <div class="iv-met-n"><span class="iv-dot" style="background:{{ $m['color'] }};"></span>{{ $m['label'] }}</div>
                        <div class="iv-met-m">{{ $gs($m['monto']) }} <span style="font-size:.74rem;color:#94a3b8;font-weight:700;">{{ $pf($p) }}</span></div>
                        <div class="iv-met-bar"><i style="width:{{ number_format($p, 1, '.', '') }}%;background:{{ $m['color'] }};"></i></div>
                        <div class="iv-met-s">{{ $fmt($m['n']) }} {{ $m['n'] === 1 ? 'operación' : 'operaciones' }}</div>
                    </div>
                @endforeach
                <div style="display:flex;justify-content:space-between;margin-top:.9rem;padding-top:.8rem;border-top:2px solid rgba(148,163,184,.4);font-weight:900;">
                    <span>Total</span><span>{{ $gs($datos['total']) }}</span>
                </div>
                <p class="iv-note">Los cobros se toman de la caja; el crédito corresponde a ventas con deuda pendiente de cobro.</p>
            @endif
        </div>

    {{-- ══════════ HORARIO ══════════ --}}
    @elseif($vista === 'horario')
        <div class="iv-grid2">
            <div class="iv-card">
                <div class="iv-card-t">Ventas por hora del día</div>
                @if(empty($datos['horas']))
                    <div class="iv-empty">No hay ventas en el período seleccionado.</div>
                @else
                    <div class="iv-chart">
                        @foreach($datos['horas'] as $x)
                            @php $h = $datos['maxHora'] > 0 ? max(2, $x['total'] / $datos['maxHora'] * 100) : 2; @endphp
                            <div class="iv-col" title="{{ sprintf('%02d:00', $x['h']) }} · {{ $gs($x['total']) }} ({{ $x['cant'] }} ventas)">
                                <div class="iv-col-bar {{ $x['total'] <= 0 ? 'zero' : '' }}" style="height: {{ number_format($h, 1, '.', '') }}%;"></div>
                                <div class="iv-col-l">{{ sprintf('%02d', $x['h']) }}h</div>
                            </div>
                        @endforeach
                    </div>
                    @php $pico = collect($datos['horas'])->sortByDesc('total')->first(); @endphp
                    <span class="iv-best">★ Hora pico: {{ sprintf('%02d:00', $pico['h']) }} · {{ $gs($pico['total']) }} ({{ $pico['cant'] }} ventas)</span>
                @endif
            </div>

            <div class="iv-card">
                <div class="iv-card-t">Ventas por día de la semana</div>
                @php $totDias = array_sum(array_column($datos['dias'], 'total')); @endphp
                @if($totDias <= 0)
                    <div class="iv-empty">No hay ventas en el período seleccionado.</div>
                @else
                    <div class="iv-wrap">
                        <table class="iv-table">
                            <thead><tr><th>Día</th><th class="r">Ventas</th><th class="r">Total</th><th>Participación</th></tr></thead>
                            <tbody>
                                @foreach($datos['dias'] as $x)
                                    <tr>
                                        <td><b>{{ $x['nombre'] }}</b></td>
                                        <td class="r">{{ $fmt($x['cant']) }}</td>
                                        <td class="r"><b>{{ $gs($x['total']) }}</b></td>
                                        <td style="min-width:110px;"><div class="iv-bar"><i style="width:{{ number_format($pct($x['total'], $totDias), 1, '.', '') }}%;"></i></div></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

</div>
</x-filament-panels::page>
