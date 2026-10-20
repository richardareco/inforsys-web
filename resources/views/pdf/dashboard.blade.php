<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 0; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #0f172a; background: #ffffff; }
    .content { padding: 0 26px 46px 26px; }

    /* ── Encabezado ── */
    .header { background: #0a3fa8; color: #ffffff; padding: 22px 26px 18px 26px; }
    .header table { width: 100%; border-collapse: collapse; }
    .header-logo { width: 62px; vertical-align: middle; }
    .header-logo img { max-height: 48px; max-width: 58px; }
    .header-title { vertical-align: middle; padding-left: 4px; }
    .header-title .empresa { font-size: 18px; font-weight: bold; color: #ffffff; letter-spacing: 0.3px; }
    .header-title .sub { font-size: 8.5px; color: #bfdbfe; margin-top: 4px; letter-spacing: 1.4px; }
    .header-right { text-align: right; vertical-align: middle; }
    .header-right .tag { font-size: 7.5px; color: #93c5fd; letter-spacing: 1.6px; }
    .header-right .period { font-size: 12px; font-weight: bold; color: #ffffff; margin-top: 3px; }
    .header-right .generated { font-size: 7.5px; color: #bfdbfe; margin-top: 5px; }
    .stripe { height: 4px; background: #0ea5e9; margin-bottom: 18px; }

    /* ── Títulos de sección ── */
    .section-title { margin: 16px 0 9px 0; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; }
    .section-title .txt { border-left: 4px solid #0ea5e9; padding-left: 8px; font-size: 9.5px; font-weight: bold; color: #0a3fa8; letter-spacing: 1.2px; text-transform: uppercase; }

    /* ── Tarjetas KPI ── */
    .kpi-table { width: 100%; border-collapse: separate; border-spacing: 6px; margin: 0 -6px; }
    .kpi { padding: 11px 12px 8px 12px; height: 62px; border-radius: 0; border-top: 3px solid #94a3b8; }
    .kpi-label { font-size: 7px; font-weight: bold; color: #64748b; letter-spacing: 1px; text-transform: uppercase; }
    .kpi-value { font-size: 13px; font-weight: bold; margin-top: 6px; }
    .kpi-sub { font-size: 7px; color: #64748b; margin-top: 4px; }
    .up { font-size: 7.5px; color: #15803d; font-weight: bold; margin-top: 4px; }
    .down { font-size: 7.5px; color: #b91c1c; font-weight: bold; margin-top: 4px; }

    /* ── Dos columnas ── */
    .two-col { width: 100%; border-collapse: separate; border-spacing: 0; }
    .two-col td.col { vertical-align: top; width: 50%; }
    .two-col td.left { padding-right: 9px; }
    .two-col td.right { padding-left: 9px; }
    .card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 11px 13px 11px 13px; background: #ffffff; }

    /* ── Barras de formas de pago ── */
    .pm-row { margin-bottom: 9px; }
    .pm-head { width: 100%; border-collapse: collapse; }
    .pm-name { font-size: 8.5px; font-weight: bold; color: #334155; }
    .pm-pct { text-align: right; font-size: 8px; color: #64748b; }
    .pm-track { background: #eef2f7; border-radius: 5px; height: 8px; width: 100%; margin-top: 3px; }
    .pm-fill { border-radius: 5px; height: 8px; }
    .pm-amount { font-size: 8px; color: #475569; font-weight: bold; margin-top: 2px; }
    .pm-total { border-top: 1px solid #e2e8f0; padding-top: 6px; margin-top: 4px; }

    /* ── Tablas de datos ── */
    .data-table { width: 100%; border-collapse: collapse; font-size: 8px; }
    .data-table th { border: 0; background: #0a3fa8; color: #ffffff; font-weight: bold; padding: 6px 7px; text-align: left; font-size: 7px; letter-spacing: 0.8px; text-transform: uppercase; }
    .data-table td { padding: 6px 7px; border-bottom: 1px solid #eef2f7; color: #334155; }
    .data-table tr.alt td { background: #f8fafc; }
    .data-table .num { text-align: right; }
    .data-table td.strong { font-weight: bold; color: #0f172a; }
    .rank { display: inline-block; width: 16px; padding: 3px 0 2px 0; border-radius: 9px; background: #e2e8f0; text-align: center; font-size: 7px; font-weight: bold; color: #475569; }
    .rank-1 { background: #fde68a; color: #854d0e; }
    .rank-2 { background: #e2e8f0; color: #334155; }
    .rank-3 { background: #fed7aa; color: #9a3412; }
    .empty { text-align: center; color: #94a3b8; padding: 14px; font-size: 8px; }

    /* ── Meta ── */
    .meta-track { background: #e2e8f0; border-radius: 6px; height: 12px; margin-top: 9px; }
    .meta-fill { border-radius: 6px; height: 12px; }

    .fixed-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 9px 26px 12px 26px; border-top: 1px solid #e2e8f0; background: #ffffff; font-size: 7px; color: #94a3b8; }
    .fixed-footer table { width: 100%; border-collapse: collapse; }
    .pagenum:before { content: counter(page); }
</style>
</head>
<body>

{{-- ===== ENCABEZADO ===== --}}
<div class="header">
    <table>
        <tr>
            @if($logoPath)
            <td class="header-logo"><img src="{{ $logoPath }}" alt="Logo"></td>
            @endif
            <td class="header-title">
                <div class="empresa">{{ $empresa?->nombre ?? 'Dashboard' }}</div>
                <div class="sub">REPORTE DE VENTAS Y GESTIÓN</div>
            </td>
            <td class="header-right">
                <div class="tag">PERÍODO</div>
                <div class="period">{{ $periodLabel }}</div>
                <div class="generated">Generado el {{ now()->isoFormat('D [de] MMMM [de] YYYY, HH:mm') }}</div>
            </td>
        </tr>
    </table>
</div>
<div class="stripe"></div>

<div class="content">

{{-- ===== INDICADORES FINANCIEROS ===== --}}
<div class="section-title" style="margin-top:2px;"><div class="txt">Indicadores financieros</div></div>

@php
    $ganPos   = $ganancia >= 0;
    $kpis = [
        [
            ['label' => 'Ventas totales', 'value' => $ventas,    'color' => '#15803d', 'bg' => '#f0fdf4', 'border' => '#22c55e', 'change' => true],
            ['label' => 'Costo',          'value' => $costo,     'color' => '#1d4ed8', 'bg' => '#eff6ff', 'border' => '#3b82f6'],
            ['label' => 'Gastos',         'value' => $gastos,    'color' => '#6d28d9', 'bg' => '#f5f3ff', 'border' => '#8b5cf6'],
            ['label' => 'Devoluciones',   'value' => $devoluciones, 'color' => '#c2410c', 'bg' => '#fff7ed', 'border' => '#f97316'],
        ],
        [
            ['label' => 'Ganancia neta',  'value' => $ganancia,  'color' => $ganPos ? '#15803d' : '#be123c', 'bg' => $ganPos ? '#f0fdf4' : '#fff1f2', 'border' => $ganPos ? '#16a34a' : '#e11d48'],
            ['label' => 'Cuentas a cobrar', 'value' => $cobrarData->total ?? 0, 'color' => '#0369a1', 'bg' => '#f0f9ff', 'border' => '#0ea5e9', 'sub' => ($cobrarData->cnt ?? 0) . ' facturas pendientes'],
            ['label' => 'Cuentas a pagar',  'value' => $pagarData->total ?? 0,  'color' => '#b45309', 'bg' => '#fffbeb', 'border' => '#f59e0b', 'sub' => ($pagarData->cnt ?? 0) . ' facturas pendientes'],
            ['label' => 'Créditos cobrados', 'value' => $creditosCobrados, 'color' => '#0f766e', 'bg' => '#f0fdfa', 'border' => '#14b8a6'],
        ],
    ];
@endphp
<table class="kpi-table">
    @foreach($kpis as $fila)
    <tr>
        @foreach($fila as $k)
        <td style="width:25%;">
            <div class="kpi" style="background:{{ $k['bg'] }}; border-top-color:{{ $k['border'] }};">
                <div class="kpi-label">{{ $k['label'] }}</div>
                <div class="kpi-value" style="color:{{ $k['color'] }};">Gs. {{ number_format((float) $k['value'], 0, ',', '.') }}</div>
                @if(!empty($k['change']) && $prevVentas > 0)
                    @php $chg = (($ventas - $prevVentas) / $prevVentas) * 100; @endphp
                    <div class="{{ $chg >= 0 ? 'up' : 'down' }}">{{ $chg >= 0 ? '▲' : '▼' }} {{ number_format(abs($chg), 1, ',', '.') }}% vs período anterior</div>
                @elseif(!empty($k['sub']))
                    <div class="kpi-sub">{{ $k['sub'] }}</div>
                @endif
            </div>
        </td>
        @endforeach
    </tr>
    @endforeach
</table>

{{-- ===== VENTAS POR MES + FORMAS DE PAGO ===== --}}
<table class="two-col" style="margin-top:6px;">
<tr>
<td class="col left">
    <div class="section-title"><div class="txt">Ventas por mes</div></div>
    <div class="card">
        @php $barMaxH = 120; $totCurr = array_sum(array_column($meses, 'curr')); $totPrev = array_sum(array_column($meses, 'prev')); @endphp
        <table style="width:100%; border-collapse:collapse;">
            <tr style="vertical-align:bottom;">
                @foreach($meses as $mes)
                <td style="text-align:center; padding:0; vertical-align:bottom; width:{{ 100/12 }}%;">
                    @php
                        $hC = $maxVenta > 0 ? round(($mes['curr'] / $maxVenta) * $barMaxH) : 0;
                        $hP = $maxVenta > 0 ? round(($mes['prev'] / $maxVenta) * $barMaxH) : 0;
                    @endphp
                    <table style="border-collapse:collapse; margin:0 auto;">
                        <tr style="vertical-align:bottom;">
                            <td style="vertical-align:bottom; padding:0 1px;"><div style="width:7px; height:{{ max(1,$hC) }}px; background:#0a6cdc; border-radius:2px 2px 0 0;"></div></td>
                            <td style="vertical-align:bottom; padding:0 1px;"><div style="width:7px; height:{{ max(1,$hP) }}px; background:#cbd5e1; border-radius:2px 2px 0 0;"></div></td>
                        </tr>
                    </table>
                    <div style="font-size:6px; color:#94a3b8; text-align:center; margin-top:3px;">{{ ucfirst(str_replace('.', '', $mes['label'])) }}</div>
                </td>
                @endforeach
            </tr>
        </table>
        <div style="margin-top:8px; font-size:7.5px; color:#475569;">
            <span style="display:inline-block; width:9px; height:7px; background:#0a6cdc; border-radius:2px; margin-right:3px;"></span> {{ $currYear }}: <b>Gs. {{ number_format($totCurr, 0, ',', '.') }}</b>
        </div>
        <div style="margin-top:4px; font-size:7.5px; color:#475569;">
            <span style="display:inline-block; width:9px; height:7px; background:#cbd5e1; border-radius:2px; margin-right:3px;"></span> {{ $prevYear }}: <b>Gs. {{ number_format($totPrev, 0, ',', '.') }}</b>
        </div>
    </div>
</td>

<td class="col right">
    <div class="section-title"><div class="txt">Formas de pago</div></div>
    <div class="card">
        @foreach($metodos as $m)
        <div class="pm-row">
            <table class="pm-head">
                <tr>
                    <td class="pm-name"><span style="display:inline-block; width:7px; height:7px; border-radius:4px; background:{{ $m['color'] }}; margin-right:4px;"></span>{{ $m['label'] === 'Credito' ? 'Crédito' : $m['label'] }}</td>
                    <td class="pm-pct">{{ $m['pct'] }}%</td>
                </tr>
            </table>
            <div class="pm-track"><div class="pm-fill" style="background:{{ $m['color'] }}; width:{{ max(1, $m['pct']) }}%;"></div></div>
            <div class="pm-amount">Gs. {{ number_format($m['amount'], 0, ',', '.') }}</div>
        </div>
        @endforeach
        <div class="pm-total">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="font-size:8.5px; font-weight:bold; color:#475569;">Total</td>
                    <td style="text-align:right; font-size:10px; font-weight:bold; color:#0a3fa8;">Gs. {{ number_format($totalPagos, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>
</td>
</tr>
</table>

{{-- ===== META DE VENTAS ===== --}}
@if($metaMes > 0)
@php
    $metaColor  = ($metaPct >= 70) ? '#16a34a' : (($metaPct >= 40) ? '#f59e0b' : '#ef4444');
    $metaBg     = ($metaPct >= 70) ? '#f0fdf4' : (($metaPct >= 40) ? '#fffbeb' : '#fef2f2');
    $metaStatus = ($metaPct >= 70) ? 'En meta' : (($metaPct >= 40) ? 'Cerca de la meta' : 'Por debajo de la meta');
    $restante   = max(0, $metaMes - $ventasMes);
@endphp
<div class="section-title"><div class="txt">Meta de ventas — {{ ucfirst(now()->isoFormat('MMMM YYYY')) }}</div></div>
<div class="card" style="background:{{ $metaBg }}; border-color:#e2e8f0;">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:28%; text-align:center; vertical-align:middle;">
                <div style="font-size:28px; font-weight:bold; color:{{ $metaColor }};">{{ $metaPct }}%</div>
                <div style="font-size:8px; font-weight:bold; color:{{ $metaColor }}; margin-top:2px;">{{ $metaStatus }}</div>
            </td>
            <td style="width:36%; vertical-align:middle; padding-left:12px;">
                <div class="kpi-label">Ventas del mes</div>
                <div class="kpi-value" style="color:#15803d;">Gs. {{ number_format($ventasMes, 0, ',', '.') }}</div>
            </td>
            <td style="width:36%; vertical-align:middle;">
                <div class="kpi-label">Meta del mes</div>
                <div class="kpi-value">Gs. {{ number_format($metaMes, 0, ',', '.') }}</div>
                @if($restante > 0)
                    <div class="kpi-sub">Faltan Gs. {{ number_format($restante, 0, ',', '.') }}</div>
                @else
                    <div class="up">¡Meta alcanzada!</div>
                @endif
            </td>
        </tr>
    </table>
    <div class="meta-track"><div class="meta-fill" style="background:{{ $metaColor }}; width:{{ max(1, $metaPct) }}%;"></div></div>
</div>
@endif

{{-- ===== TOP PRODUCTOS + TOP CLIENTES ===== --}}
<table class="two-col" style="margin-top:4px;">
<tr>
<td class="col left">
    <div class="section-title"><div class="txt">Top productos</div></div>
    <table class="data-table">
        <thead><tr><th style="width:22px;">#</th><th>Producto</th><th class="num">Cant.</th><th class="num">Ingresos</th></tr></thead>
        <tbody>
            @forelse($topProductos as $i => $p)
            <tr class="{{ $i % 2 ? 'alt' : '' }}">
                <td><span class="rank {{ $i==0?'rank-1':($i==1?'rank-2':($i==2?'rank-3':'')) }}">{{ $i+1 }}</span></td>
                <td>{{ \Illuminate\Support\Str::limit($p->descr, 22) }}</td>
                <td class="num">{{ number_format($p->ventas, 0, ',', '.') }}</td>
                <td class="num strong">Gs. {{ number_format($p->ingresos, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="empty">Sin datos para el período</td></tr>
            @endforelse
        </tbody>
    </table>
</td>
<td class="col right">
    <div class="section-title"><div class="txt">Top clientes</div></div>
    <table class="data-table">
        <thead><tr><th style="width:22px;">#</th><th>Cliente</th><th class="num">Pedidos</th><th class="num">Total</th></tr></thead>
        <tbody>
            @forelse($topClientes as $i => $c)
            <tr class="{{ $i % 2 ? 'alt' : '' }}">
                <td><span class="rank {{ $i==0?'rank-1':($i==1?'rank-2':($i==2?'rank-3':'')) }}">{{ $i+1 }}</span></td>
                <td>{{ \Illuminate\Support\Str::limit($c->custname, 22) }}</td>
                <td class="num">{{ $c->pedidos }}</td>
                <td class="num strong">Gs. {{ number_format($c->ingresos, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="empty">Sin datos para el período</td></tr>
            @endforelse
        </tbody>
    </table>
</td>
</tr>
</table>

{{-- ===== VENDEDORES ===== --}}
@if($vendedores->isNotEmpty())
<div class="section-title"><div class="txt">Vendedores</div></div>
<table class="data-table">
    <thead>
        <tr>
            <th style="width:22px;">#</th><th>Vendedor</th>
            <th class="num">Ventas</th><th class="num">Total ventas</th>
            <th class="num">Comisión %</th><th class="num">Comisión est.</th>
        </tr>
    </thead>
    <tbody>
        @foreach($vendedores as $i => $v)
        <tr class="{{ $i % 2 ? 'alt' : '' }}">
            <td><span class="rank {{ $i==0?'rank-1':($i==1?'rank-2':($i==2?'rank-3':'')) }}">{{ $i+1 }}</span></td>
            <td class="strong">{{ $v->pername }}</td>
            <td class="num">{{ $v->ventas_count }}</td>
            <td class="num strong">Gs. {{ number_format($v->total_ventas, 0, ',', '.') }}</td>
            <td class="num">{{ rtrim(rtrim(number_format((float) $v->porc_comision, 2, ',', '.'), '0'), ',') }}%</td>
            <td class="num">Gs. {{ number_format($v->total_ventas * ($v->porc_comision / 100), 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

</div>

{{-- ===== PIE FIJO ===== --}}
<div class="fixed-footer">
    <table>
        <tr>
            <td>{{ $empresa?->nombre }} · Reporte generado por Inforsys el {{ now()->isoFormat('D [de] MMMM [de] YYYY') }} a las {{ now()->format('H:i') }} hs</td>
            <td style="text-align:right;">Página <span class="pagenum"></span></td>
        </tr>
    </table>
</div>


</body>
</html>
