<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size:10px; color:#1e293b; background:#ffffff; }

/* ── Barra de acento ── */
.top-bar { background:#0047AB; height:6px; width:100%; display:block; }

/* ── Header ── */
.header-table { width:100%; padding:20px 28px 16px; }
.logo-img { max-height:60px; max-width:130px; }
.company-name { font-size:17px; font-weight:bold; color:#0f172a; line-height:1.2; }
.company-sub  { font-size:8px; color:#94a3b8; margin-top:3px; text-transform:uppercase; letter-spacing:0.6px; }
.doc-title { font-size:26px; font-weight:bold; color:#0047AB; letter-spacing:3px; text-transform:uppercase; }
.doc-number { font-size:13px; font-weight:bold; color:#1e293b; margin-top:6px; }
.doc-date   { font-size:9px;  color:#94a3b8; margin-top:3px; }

/* ── Separador ── */
.divider { border:none; border-top:1px solid #e2e8f0; margin:0 28px; }

/* ── Sección cliente ── */
.client-wrap { padding:14px 28px; }
.client-box {
    background:#f8fafc;
    border-left:4px solid #0047AB;
    padding:12px 16px;
}
.client-tag  { font-size:7.5px; font-weight:bold; text-transform:uppercase; letter-spacing:1.2px; color:#0047AB; margin-bottom:6px; }
.client-name { font-size:13px; font-weight:bold; color:#0f172a; }
.client-meta { font-size:8.5px; color:#64748b; margin-top:5px; line-height:1.7; }
.client-meta .lbl { color:#94a3b8; margin-right:4px; }

/* ── Tabla de ítems ── */
.items-wrap { padding:14px 28px 0; }
.section-lbl {
    font-size:7.5px; font-weight:bold; text-transform:uppercase;
    letter-spacing:1px; color:#0047AB;
    border-bottom:2px solid #0047AB;
    padding-bottom:5px; margin-bottom:10px;
}
.items-table { width:100%; border-collapse:collapse; font-size:9.5px; }
.items-table thead tr { background:#0047AB; }
.items-table thead th {
    padding:8px 10px; color:#ffffff;
    font-size:8px; font-weight:bold;
    text-transform:uppercase; letter-spacing:0.6px;
    text-align:left;
}
.items-table thead th.num { text-align:right; }
.items-table tbody td { padding:8px 10px; border-bottom:1px solid #f1f5f9; vertical-align:top; }
.items-table tbody tr:nth-child(even) td { background:#f8fafc; }
.items-table tbody tr:last-child td { border-bottom:none; }
.items-table td.num { text-align:right; }
.item-descr { font-weight:600; color:#0f172a; }
.item-code  { font-size:8px; color:#94a3b8; margin-top:2px; }

/* ── Totales ── */
.totales-wrap { padding:8px 28px 0; }
.totales { width:36%; margin-left:64%; border-collapse:collapse; font-size:10px; }
.totales td { padding:5px 10px; }
.t-lbl { color:#64748b; }
.t-val { text-align:right; font-weight:600; color:#1e293b; }
.t-dscto { color:#dc2626; }
.t-total { background:#0047AB; }
.t-total td { color:#ffffff; font-weight:bold; font-size:13px; padding:9px 10px; }

/* ── Observaciones ── */
.obs-box {
    margin:16px 28px 0;
    background:#fffbeb;
    border-left:3px solid #f59e0b;
    padding:10px 14px;
    font-size:9px; color:#92400e;
}
.obs-box .obs-lbl { font-weight:bold; color:#78350f; margin-bottom:3px; font-size:8px; text-transform:uppercase; letter-spacing:0.5px; }

/* ── Validez ── */
.validez { text-align:center; margin:16px 28px 0; font-size:8.5px; color:#94a3b8; font-style:italic; }

/* ── Footer ── */
.footer { margin-top:20px; border-top:1px solid #e2e8f0; padding:10px 28px; }
.footer table { width:100%; }
.footer-left  { font-size:8px; color:#94a3b8; }
.footer-brand { font-size:9px; font-weight:bold; color:#0047AB; display:block; margin-bottom:2px; }
.footer-right { font-size:8px; color:#94a3b8; text-align:right; }
</style>
</head>
<body>

{{-- ══ BARRA DE ACENTO ══ --}}
<div class="top-bar"></div>

{{-- ══ HEADER ══ --}}
<table class="header-table">
    <tr>
        {{-- Izquierda: logo o nombre --}}
        <td style="vertical-align:middle;width:55%;">
            @if($logoPath)
                <img class="logo-img" src="{{ $logoPath }}" alt="Logo">
                @if($empresa?->nombre)
                    <div class="company-sub" style="margin-top:6px;">{{ $empresa->nombre }}</div>
                @endif
            @else
                <div class="company-name">{{ $empresa?->nombre ?? 'Empresa' }}</div>
                <div class="company-sub">Sistema Inforsys PDV</div>
            @endif
        </td>
        {{-- Derecha: título y datos del documento --}}
        <td style="text-align:right;vertical-align:top;">
            <div class="doc-title">Presupuesto</div>
            <div class="doc-number">N.° &nbsp;{{ $presupuesto->presnr }}</div>
            <div class="doc-date">{{ \Carbon\Carbon::parse($presupuesto->fecha)->format('d \d\e F \d\e Y') }}</div>
        </td>
    </tr>
</table>

<hr class="divider">

{{-- ══ DATOS DEL CLIENTE ══ --}}
<div class="client-wrap">
    <div class="client-box">
        <div class="client-tag">Presupuesto para</div>
        <div class="client-name">{{ $cliente?->custname ?? $presupuesto->custnr }}</div>
        <div class="client-meta">
            @if($cliente?->ruc)
                <span><span class="lbl">RUC:</span>{{ $cliente->ruc }}</span>&nbsp;&nbsp;
            @endif
            @if($cliente?->telef1)
                <span><span class="lbl">Tel.:</span>{{ $cliente->telef1 }}</span>&nbsp;&nbsp;
            @endif
            @if($cliente?->celular1)
                <span><span class="lbl">Cel.:</span>{{ $cliente->celular1 }}</span>
            @endif
            @if($cliente?->addr)
                <br><span class="lbl">Dir.:</span>{{ $cliente->addr }}
            @endif
        </div>
    </div>
</div>

{{-- ══ TABLA DE ÍTEMS ══ --}}
<div class="items-wrap">
    <div class="section-lbl">Detalle de Ítems</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:2.2rem;">#</th>
                <th style="width:6rem;">Código</th>
                <th>Descripción</th>
                <th class="num" style="width:5rem;">Cant.</th>
                <th class="num" style="width:8rem;">Precio Unit.</th>
                <th class="num" style="width:9rem;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $item)
            <tr>
                <td style="color:#94a3b8;font-size:9px;">{{ $i + 1 }}</td>
                <td class="item-code" style="font-size:9px;color:#64748b;">{{ $item->item }}</td>
                <td>
                    <div class="item-descr">{{ $item->descr }}</div>
                </td>
                <td class="num">{{ number_format($item->qty, 0, ',', '.') }}</td>
                <td class="num">Gs.&nbsp;{{ number_format($item->precio, 0, ',', '.') }}</td>
                <td class="num" style="font-weight:700;">Gs.&nbsp;{{ number_format($item->qty * $item->precio, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ══ TOTALES ══ --}}
<div class="totales-wrap">
    <table class="totales">
        @if($presupuesto->dscto > 0)
        <tr>
            <td class="t-lbl">Subtotal:</td>
            <td class="t-val">Gs.&nbsp;{{ number_format($presupuesto->total + $presupuesto->dscto, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="t-lbl">Descuento&nbsp;({{ $presupuesto->pdscto }}%):</td>
            <td class="t-val t-dscto">−&nbsp;Gs.&nbsp;{{ number_format($presupuesto->dscto, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="t-total">
            <td style="color:#ffffff;font-weight:bold;font-size:13px;padding:9px 10px;">TOTAL</td>
            <td style="text-align:right;color:#ffffff;font-weight:bold;font-size:13px;padding:9px 10px;">Gs.&nbsp;{{ number_format($presupuesto->total, 0, ',', '.') }}</td>
        </tr>
    </table>
</div>

{{-- ══ OBSERVACIONES ══ --}}
@if($presupuesto->obs)
<div class="obs-box">
    <div class="obs-lbl">Observaciones</div>
    {{ $presupuesto->obs }}
</div>
@endif

{{-- ══ VALIDEZ ══ --}}
<div class="validez">
    Este presupuesto tiene una validez de 30 días a partir de la fecha de emisión.
</div>

{{-- ══ FOOTER ══ --}}
<div class="footer">
    <table>
        <tr>
            <td class="footer-left">
                <span class="footer-brand">{{ $empresa?->nombre ?? 'Inforsys PDV' }}</span>
                Sistema Inforsys PDV
            </td>
            <td class="footer-right">
                Generado el {{ now()->format('d/m/Y H:i') }}
            </td>
        </tr>
    </table>
</div>

</body>
</html>
