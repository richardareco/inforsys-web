<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; background: #fff; }

    /* Header */
    .header { background: #0f172a; color: #f8fafc; padding: 16px 20px; margin-bottom: 14px; }
    .header table { width: 100%; }
    .header-logo { width: 60px; }
    .header-logo img { max-height: 50px; max-width: 55px; }
    .header-title { padding-left: 12px; }
    .header-title .empresa { font-size: 15px; font-weight: bold; color: #f8fafc; }
    .header-title .sub { font-size: 9px; color: #94a3b8; margin-top: 3px; }
    .header-right { text-align: right; }
    .header-right .period { font-size: 11px; font-weight: bold; color: #3b82f6; }
    .header-right .generated { font-size: 8px; color: #64748b; margin-top: 4px; }

    /* Section title */
    .section-title { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #64748b; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 10px; margin-top: 16px; }

    /* KPI cards */
    .kpi-table { width: 100%; border-collapse: separate; border-spacing: 5px; }
    .kpi-card { padding: 10px 12px; border-radius: 8px; border: 1px solid #e2e8f0; }
    .kpi-label { font-size: 8px; color: #64748b; margin-bottom: 4px; }
    .kpi-value { font-size: 12px; font-weight: bold; color: #0f172a; }
    .kpi-sub { font-size: 7.5px; color: #94a3b8; margin-top: 2px; }
    .kpi-change-up { font-size: 8px; color: #16a34a; }
    .kpi-change-down { font-size: 8px; color: #dc2626; }

    /* Métodos de pago */
    .metodos-table { width: 100%; border-collapse: collapse; }
    .metodo-row { margin-bottom: 8px; }
    .bar-track { background: #f1f5f9; border-radius: 4px; height: 10px; width: 100%; }
    .bar-fill { border-radius: 4px; height: 10px; }

    /* Ventas por mes */
    .chart-table { width: 100%; border-collapse: collapse; }
    .chart-bar-curr { background: #3b82f6; border-radius: 2px 2px 0 0; }
    .chart-bar-prev { background: #cbd5e1; border-radius: 2px 2px 0 0; }
    .chart-label { font-size: 7px; color: #64748b; text-align: center; }

    /* Gauge simple */
    .meta-box { text-align: center; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; }
    .meta-pct { font-size: 30px; font-weight: bold; }
    .meta-label { font-size: 9px; color: #64748b; margin-top: 4px; }
    .meta-amounts { font-size: 8px; color: #94a3b8; margin-top: 6px; }

    /* Tables */
    .data-table { width: 100%; border-collapse: collapse; font-size: 8.5px; }
    .data-table th { background: #f1f5f9; color: #475569; font-weight: bold; padding: 5px 8px; text-align: left; }
    .data-table td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; color: #334155; }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table .num { text-align: right; }
    .rank { display: inline-block; width: 16px; height: 16px; border-radius: 50%; background: #e2e8f0; text-align: center; line-height: 16px; font-size: 7.5px; font-weight: bold; color: #475569; }
    .rank-1 { background: #fef9c3; color: #854d0e; }
    .rank-2 { background: #f1f5f9; color: #475569; }
    .rank-3 { background: #fef3c7; color: #92400e; }

    /* Two column layout */
    .two-col { width: 100%; border-collapse: separate; border-spacing: 8px 0; }
    .two-col td { vertical-align: top; width: 50%; }

    /* Gauge arc drawn with border trick */
    .gauge-wrap { position: relative; text-align: center; }
    .gauge-outer { width: 140px; height: 70px; border-radius: 70px 70px 0 0; overflow: hidden; margin: 0 auto; background: #e2e8f0; position: relative; }
    .gauge-mask { width: 100px; height: 50px; border-radius: 50px 50px 0 0; position: absolute; bottom: 0; left: 20px; background: #fff; }
    .gauge-fill { width: 140px; height: 70px; border-radius: 70px 70px 0 0; overflow: hidden; position: absolute; top: 0; left: 0; transform-origin: 50% 100%; }

    .footer { margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 8px; text-align: center; font-size: 7.5px; color: #94a3b8; }
</style>
</head>
<body>

{{-- ===== HEADER ===== --}}
<div class="header">
    <table>
        <tr>
            @if($logoPath)
            <td class="header-logo">
                <img src="{{ $logoPath }}" alt="Logo">
            </td>
            @endif
            <td class="header-title">
                <div class="empresa">{{ $empresa?->nombre ?? 'Dashboard' }}</div>
                <div class="sub">Reporte de Ventas — {{ $periodLabel }}</div>
            </td>
            <td class="header-right">
                <div class="period">{{ $periodLabel }}</div>
                <div class="generated">Generado: {{ now()->isoFormat('D [de] MMMM [de] YYYY, H:mm') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- ===== KPI FINANCIERO ===== --}}
<div class="section-title">Indicadores Financieros</div>
<table class="kpi-table">
    <tr>
        <td style="padding:3px;">
            <div class="kpi-card" style="background:rgba(22,163,74,0.08);">
                <div class="kpi-label">Ventas Totales</div>
                <div class="kpi-value" style="color:#15803d;">Gs. {{ number_format($ventas, 0, ',', '.') }}</div>
                @if($prevVentas > 0)
                    @php $chg = (($ventas - $prevVentas) / $prevVentas) * 100; @endphp
                    <div class="{{ $chg >= 0 ? 'kpi-change-up' : 'kpi-change-down' }}">{{ $chg >= 0 ? '▲' : '▼' }} {{ number_format(abs($chg), 1) }}% vs anterior</div>
                @endif
            </div>
        </td>
        <td style="padding:3px;">
            <div class="kpi-card" style="background:rgba(37,99,235,0.08);">
                <div class="kpi-label">Costo</div>
                <div class="kpi-value" style="color:#1d4ed8;">Gs. {{ number_format($costo, 0, ',', '.') }}</div>
            </div>
        </td>
        <td style="padding:3px;">
            <div class="kpi-card" style="background:rgba(124,58,237,0.08);">
                <div class="kpi-label">Gastos</div>
                <div class="kpi-value" style="color:#6d28d9;">Gs. {{ number_format($gastos, 0, ',', '.') }}</div>
            </div>
        </td>
        <td style="padding:3px;">
            <div class="kpi-card" style="background:rgba(234,88,12,0.08);">
                <div class="kpi-label">Devoluciones</div>
                <div class="kpi-value" style="color:#c2410c;">Gs. {{ number_format($devoluciones, 0, ',', '.') }}</div>
            </div>
        </td>
    </tr>
    <tr>
        <td style="padding:3px;">
            @php $ganBg = $ganancia >= 0 ? 'rgba(22,163,74,0.08)' : 'rgba(225,29,72,0.08)'; $ganColor = $ganancia >= 0 ? '#15803d' : '#be123c'; @endphp
            <div class="kpi-card" style="background:{{ $ganBg }};">
                <div class="kpi-label">Ganancia Neta</div>
                <div class="kpi-value" style="color:{{ $ganColor }};">Gs. {{ number_format($ganancia, 0, ',', '.') }}</div>
            </div>
        </td>
        <td style="padding:3px;">
            <div class="kpi-card" style="background:rgba(2,132,199,0.08);">
                <div class="kpi-label">Cuentas a Cobrar</div>
                <div class="kpi-value" style="color:#0369a1;">Gs. {{ number_format($cobrarData->total, 0, ',', '.') }}</div>
                <div class="kpi-sub">{{ $cobrarData->cnt }} facturas pendientes</div>
            </div>
        </td>
        <td style="padding:3px;">
            <div class="kpi-card" style="background:rgba(217,119,6,0.08);">
                <div class="kpi-label">Cuentas a Pagar</div>
                <div class="kpi-value" style="color:#b45309;">Gs. {{ number_format($pagarData->total, 0, ',', '.') }}</div>
                <div class="kpi-sub">{{ $pagarData->cnt }} facturas pendientes</div>
            </div>
        </td>
        <td style="padding:3px;">
            <div class="kpi-card" style="background:rgba(13,148,136,0.08);">
                <div class="kpi-label">Créditos Cobrados</div>
                <div class="kpi-value" style="color:#0f766e;">Gs. {{ number_format($creditosCobrados, 0, ',', '.') }}</div>
            </div>
        </td>
    </tr>
</table>

{{-- ===== VENTAS POR MES + FORMAS DE PAGO ===== --}}
<table class="two-col" style="margin-top:4px;">
<tr>
<td>
    <div class="section-title">Ventas por Mes — {{ $currYear }} vs {{ $prevYear }}</div>
    @php $barMaxH = 36; $barW = 8; $spacing = 4; @endphp
    <table style="width:100%; border-collapse:collapse;">
        <tr style="vertical-align:bottom;">
            @foreach($meses as $mes)
            <td style="text-align:center; padding:0 1px; vertical-align:bottom; width:{{ 100/12 }}%;">
                @php
                    $hC = $maxVenta > 0 ? round(($mes['curr'] / $maxVenta) * $barMaxH) : 0;
                    $hP = $maxVenta > 0 ? round(($mes['prev'] / $maxVenta) * $barMaxH) : 0;
                @endphp
                <table style="border-collapse:collapse; margin:0 auto;">
                    <tr style="vertical-align:bottom;">
                        <td style="vertical-align:bottom; padding:0 1px;">
                            <div style="width:7px; height:{{ max(1,$hC) }}px; background:#3b82f6; border-radius:2px 2px 0 0;"></div>
                        </td>
                        <td style="vertical-align:bottom; padding:0 1px;">
                            <div style="width:7px; height:{{ max(1,$hP) }}px; background:#cbd5e1; border-radius:2px 2px 0 0;"></div>
                        </td>
                    </tr>
                </table>
                <div style="font-size:6.5px; color:#94a3b8; text-align:center; margin-top:2px;">{{ $mes['label'] }}</div>
            </td>
            @endforeach
        </tr>
    </table>
    <div style="margin-top:6px; font-size:7.5px; color:#64748b;">
        <span style="display:inline-block; width:10px; height:8px; background:#3b82f6; border-radius:2px; margin-right:4px; vertical-align:middle;"></span> {{ $currYear }}
        &nbsp;&nbsp;
        <span style="display:inline-block; width:10px; height:8px; background:#cbd5e1; border-radius:2px; margin-right:4px; vertical-align:middle;"></span> {{ $prevYear }}
    </div>
</td>

<td>
    <div class="section-title">Formas de Pago</div>
    @foreach($metodos as $m)
    <div style="margin-bottom:9px;">
        <table style="width:100%; border-collapse:collapse; margin-bottom:3px;">
            <tr>
                <td style="font-size:8.5px; color:#475569; font-weight:bold;">{{ $m['label'] }}</td>
                <td style="text-align:right; font-size:8px; color:#64748b;">{{ $m['pct'] }}%</td>
            </tr>
        </table>
        <div style="background:#f1f5f9; border-radius:3px; height:9px; width:100%;">
            <div style="background:{{ $m['color'] }}; border-radius:3px; height:9px; width:{{ max(1,$m['pct']) }}%;"></div>
        </div>
        <div style="font-size:8px; color:#475569; font-weight:600; margin-top:2px;">Gs. {{ number_format($m['amount'], 0, ',', '.') }}</div>
    </div>
    @endforeach
    <div style="border-top:1px solid #e2e8f0; padding-top:5px; margin-top:2px;">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="font-size:8.5px; color:#475569; font-weight:bold;">Total</td>
                <td style="text-align:right; font-size:9px; font-weight:bold; color:#0f172a;">Gs. {{ number_format($totalPagos, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
</td>
</tr>
</table>

{{-- ===== META DE VENTAS ===== --}}
@if($metaMes > 0)
<div class="section-title">Meta de Ventas — {{ now()->isoFormat('MMMM YYYY') }}</div>
@php
    $metaColor = ($metaPct >= 70) ? '#22c55e' : (($metaPct >= 40) ? '#f59e0b' : '#ef4444');
    $metaStatus = ($metaPct >= 70) ? 'En Meta' : (($metaPct >= 40) ? 'Cerca de la Meta' : 'Por debajo de la Meta');
@endphp
<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td style="width:33%; padding:4px;">
            <div class="kpi-card" style="text-align:center; background:rgba(100,116,139,0.05);">
                <div style="font-size:26px; font-weight:bold; color:{{ $metaColor }};">{{ $metaPct }}%</div>
                <div style="font-size:9px; font-weight:bold; color:{{ $metaColor }}; margin:3px 0;">{{ $metaStatus }}</div>
            </div>
        </td>
        <td style="width:33%; padding:4px;">
            <div class="kpi-card" style="background:rgba(34,197,94,0.08);">
                <div class="kpi-label">Ventas del Mes</div>
                <div class="kpi-value" style="color:#15803d;">Gs. {{ number_format($ventasMes, 0, ',', '.') }}</div>
            </div>
        </td>
        <td style="width:33%; padding:4px;">
            <div class="kpi-card" style="background:rgba(100,116,139,0.06);">
                <div class="kpi-label">Meta del Mes</div>
                <div class="kpi-value">Gs. {{ number_format($metaMes, 0, ',', '.') }}</div>
                @php $restante = max(0, $metaMes - $ventasMes); @endphp
                @if($restante > 0)
                    <div class="kpi-sub">Faltan Gs. {{ number_format($restante, 0, ',', '.') }}</div>
                @else
                    <div style="font-size:8px; color:#15803d; font-weight:bold;">¡Meta alcanzada!</div>
                @endif
            </div>
        </td>
    </tr>
</table>
{{-- Barra de progreso de meta --}}
<div style="margin: 6px 4px; background:#f1f5f9; border-radius:4px; height:12px;">
    <div style="background:{{ $metaColor }}; border-radius:4px; height:12px; width:{{ $metaPct }}%;"></div>
</div>
@endif

{{-- ===== TOP PRODUCTOS + TOP CLIENTES ===== --}}
<table class="two-col" style="margin-top:4px;">
<tr>
<td>
    <div class="section-title">Top Productos</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th class="num">Cant.</th>
                <th class="num">Ingresos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topProductos as $i => $p)
            <tr>
                <td><span class="rank {{ $i==0?'rank-1':($i==1?'rank-2':($i==2?'rank-3':'')) }}">{{ $i+1 }}</span></td>
                <td>{{ \Illuminate\Support\Str::limit($p->descr, 22) }}</td>
                <td class="num">{{ number_format($p->ventas, 0, ',', '.') }}</td>
                <td class="num">Gs. {{ number_format($p->ingresos, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</td>
<td>
    <div class="section-title">Top Clientes</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th class="num">Pedidos</th>
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topClientes as $i => $c)
            <tr>
                <td><span class="rank {{ $i==0?'rank-1':($i==1?'rank-2':($i==2?'rank-3':'')) }}">{{ $i+1 }}</span></td>
                <td>{{ \Illuminate\Support\Str::limit($c->custname, 22) }}</td>
                <td class="num">{{ $c->pedidos }}</td>
                <td class="num">Gs. {{ number_format($c->ingresos, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</td>
</tr>
</table>

{{-- ===== VENDEDORES ===== --}}
@if($vendedores->isNotEmpty())
<div class="section-title">Vendedores</div>
<table class="data-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Vendedor</th>
            <th class="num">Ventas</th>
            <th class="num">Total Ventas</th>
            <th class="num">Comisión %</th>
            <th class="num">Est. Comisión</th>
        </tr>
    </thead>
    <tbody>
        @foreach($vendedores as $i => $v)
        <tr>
            <td><span class="rank {{ $i==0?'rank-1':($i==1?'rank-2':($i==2?'rank-3':'')) }}">{{ $i+1 }}</span></td>
            <td>{{ $v->pername }}</td>
            <td class="num">{{ $v->ventas_count }}</td>
            <td class="num">Gs. {{ number_format($v->total_ventas, 0, ',', '.') }}</td>
            <td class="num">{{ $v->porc_comision }}%</td>
            <td class="num">Gs. {{ number_format($v->total_ventas * ($v->porc_comision / 100), 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ===== FOOTER ===== --}}
<div class="footer">
    Generado el {{ now()->isoFormat('D [de] MMMM [de] YYYY') }} a las {{ now()->format('H:i') }} hs
    &nbsp;·&nbsp; {{ $empresa?->nombre }}
    &nbsp;·&nbsp; Sistema Inforsys PDV
</div>

</body>
</html>
