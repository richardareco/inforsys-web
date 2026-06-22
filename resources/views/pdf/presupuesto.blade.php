<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:10px; color:#1e293b; }

    .header { background:#0f172a; color:#f8fafc; padding:16px 20px; margin-bottom:18px; }
    .header table { width:100%; }
    .header-logo img { max-height:55px; max-width:60px; }
    .header-empresa { padding-left:12px; }
    .header-empresa .nombre { font-size:15px; font-weight:bold; }
    .header-empresa .sub { font-size:9px; color:#94a3b8; margin-top:3px; }
    .header-right { text-align:right; }
    .header-right .titulo { font-size:18px; font-weight:bold; color:#3b82f6; }
    .header-right .nro { font-size:11px; color:#94a3b8; margin-top:4px; }

    .section { margin-bottom:14px; }
    .section-title { font-size:8.5px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:#64748b; border-bottom:1px solid #e2e8f0; padding-bottom:4px; margin-bottom:8px; }

    .info-table { width:100%; border-collapse:collapse; }
    .info-table td { padding:4px 8px; font-size:9.5px; }
    .info-table .label { color:#64748b; width:25%; }
    .info-table .value { color:#0f172a; font-weight:600; }

    .items-table { width:100%; border-collapse:collapse; font-size:9.5px; }
    .items-table th { background:#f1f5f9; color:#475569; font-weight:bold; padding:6px 8px; text-align:left; border-bottom:2px solid #e2e8f0; }
    .items-table td { padding:6px 8px; border-bottom:1px solid #f1f5f9; }
    .items-table tr:last-child td { border-bottom:none; }
    .items-table .num { text-align:right; }
    .items-table tr:nth-child(even) td { background:#f8fafc; }

    .totales { width:40%; margin-left:60%; border-collapse:collapse; font-size:10px; }
    .totales td { padding:5px 8px; }
    .totales .label { color:#64748b; }
    .totales .value { text-align:right; font-weight:600; }
    .totales .total-row td { background:#0f172a; color:#f8fafc; font-size:11px; font-weight:bold; padding:7px 8px; }

    .obs-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px; font-size:9.5px; color:#475569; margin-top:12px; }

    .footer { margin-top:20px; border-top:1px solid #e2e8f0; padding-top:8px; text-align:center; font-size:8px; color:#94a3b8; }
    .validez { text-align:center; font-size:9px; color:#64748b; margin-top:10px; font-style:italic; }
</style>
</head>
<body>

{{-- HEADER --}}
<div class="header">
    <table>
        <tr>
            @if($logoPath)
            <td style="width:65px;">
                <img src="{{ $logoPath }}" alt="Logo">
            </td>
            @endif
            <td class="header-empresa">
                <div class="nombre">{{ $empresa?->nombre ?? 'Presupuesto' }}</div>
                <div class="sub">Sistema Inforsys PDV</div>
            </td>
            <td class="header-right">
                <div class="titulo">PRESUPUESTO</div>
                <div class="nro">Nro. {{ $presupuesto->presnr }}</div>
                <div class="nro">Fecha: {{ \Carbon\Carbon::parse($presupuesto->fecha)->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- DATOS DEL CLIENTE --}}
<div class="section">
    <div class="section-title">Datos del Cliente</div>
    <table class="info-table">
        <tr>
            <td class="label">Cliente:</td>
            <td class="value">{{ $cliente?->custname ?? $presupuesto->custnr }}</td>
            <td class="label">RUC:</td>
            <td class="value">{{ $cliente?->ruc ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Teléfono:</td>
            <td class="value">{{ $cliente?->telef1 ?? '—' }}</td>
            <td class="label">Celular:</td>
            <td class="value">{{ $cliente?->celular1 ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Dirección:</td>
            <td class="value" colspan="3">{{ $cliente?->addr ?? '—' }}</td>
        </tr>
    </table>
</div>

{{-- ÍTEMS --}}
<div class="section">
    <div class="section-title">Detalle de Ítems</div>
    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Código</th>
                <th>Descripción</th>
                <th class="num">Cant.</th>
                <th class="num">Precio Unit.</th>
                <th class="num">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->item }}</td>
                <td>{{ $item->descr }}</td>
                <td class="num">{{ number_format($item->qty, 0, ',', '.') }}</td>
                <td class="num">Gs. {{ number_format($item->precio, 0, ',', '.') }}</td>
                <td class="num">Gs. {{ number_format($item->qty * $item->precio, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- TOTALES --}}
<table class="totales">
    @if($presupuesto->dscto > 0)
    <tr>
        <td class="label">Subtotal:</td>
        <td class="value">Gs. {{ number_format($presupuesto->total + $presupuesto->dscto, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td class="label">Descuento ({{ $presupuesto->pdscto }}%):</td>
        <td class="value" style="color:#dc2626;">- Gs. {{ number_format($presupuesto->dscto, 0, ',', '.') }}</td>
    </tr>
    @endif
    <tr class="total-row">
        <td>TOTAL:</td>
        <td style="text-align:right;">Gs. {{ number_format($presupuesto->total, 0, ',', '.') }}</td>
    </tr>
</table>

{{-- OBSERVACIONES --}}
@if($presupuesto->obs)
<div class="obs-box">
    <strong>Observaciones:</strong> {{ $presupuesto->obs }}
</div>
@endif

<div class="validez">* Este presupuesto tiene una validez de 30 días a partir de la fecha de emisión.</div>

{{-- FOOTER --}}
<div class="footer">
    {{ $empresa?->nombre }} &nbsp;·&nbsp; Generado el {{ now()->format('d/m/Y H:i') }} &nbsp;·&nbsp; Sistema Inforsys PDV
</div>

</body>
</html>
