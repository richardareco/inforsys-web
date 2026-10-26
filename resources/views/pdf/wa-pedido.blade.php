<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size:10px; color:#1e293b; background:#ffffff; }
.top-bar { background:#0047AB; height:6px; width:100%; display:block; }
.header-table { width:100%; padding:20px 28px 16px; }
.logo-img { max-height:60px; max-width:130px; }
.company-name { font-size:17px; font-weight:bold; color:#0f172a; }
.company-sub { font-size:8px; color:#94a3b8; margin-top:3px; text-transform:uppercase; letter-spacing:0.6px; }
.doc-title { font-size:24px; font-weight:bold; color:#0047AB; letter-spacing:3px; text-transform:uppercase; }
.doc-number { font-size:13px; font-weight:bold; color:#1e293b; margin-top:6px; }
.doc-date { font-size:9px; color:#94a3b8; margin-top:3px; }
.divider { border:none; border-top:1px solid #e2e8f0; margin:0 28px; }
.client-wrap { padding:14px 28px; }
.client-box { background:#f8fafc; border-left:4px solid #0047AB; padding:12px 16px; }
.client-tag { font-size:7.5px; font-weight:bold; text-transform:uppercase; letter-spacing:1.2px; color:#0047AB; margin-bottom:6px; }
.client-name { font-size:13px; font-weight:bold; color:#0f172a; }
.client-meta { font-size:8.5px; color:#64748b; margin-top:5px; line-height:1.7; }
.items-wrap { padding:14px 28px 0; }
.items-table { width:100%; border-collapse:collapse; font-size:9.5px; }
.items-table thead tr { background:#0047AB; }
.items-table thead th { padding:8px 10px; color:#ffffff; font-size:8px; font-weight:bold; text-transform:uppercase; letter-spacing:0.6px; text-align:left; }
.items-table thead th.num { text-align:right; }
.items-table tbody td { padding:8px 10px; border-bottom:1px solid #f1f5f9; vertical-align:top; }
.items-table tbody tr:nth-child(even) td { background:#f8fafc; }
.items-table td.num { text-align:right; }
.item-descr { font-weight:bold; color:#0f172a; }
.item-code { font-size:8px; color:#94a3b8; margin-top:2px; }
.totales-wrap { padding:8px 28px 0; }
.totales { width:40%; margin-left:60%; border-collapse:collapse; }
.t-total { background:#0047AB; }
.t-total td { color:#ffffff; font-weight:bold; font-size:13px; padding:9px 10px; }
.pie { margin:16px 28px 0; background:#fffbeb; border-left:3px solid #f59e0b; padding:10px 14px; font-size:9px; color:#92400e; }
.sello { display:inline-block; border:2px solid #16a34a; color:#16a34a; font-weight:bold; font-size:12px; letter-spacing:2px; padding:3px 10px; margin-top:6px; }
.footer { margin-top:24px; border-top:1px solid #e2e8f0; padding:10px 28px; font-size:8px; color:#94a3b8; }
</style>
</head>
<body>
<div class="top-bar"></div>

<table class="header-table">
    <tr>
        <td style="vertical-align:middle;width:55%;">
            @if($logoPath)
                <img class="logo-img" src="{{ $logoPath }}" alt="Logo">
                <div class="company-sub" style="margin-top:6px;">{{ $empresa->nombre }}</div>
            @else
                <div class="company-name">{{ $empresa->nombre }}</div>
            @endif
        </td>
        <td style="text-align:right;vertical-align:top;">
            <div class="doc-title">Pedido</div>
            <div class="doc-number">N.° {{ $pedido->numero }}</div>
            <div class="doc-date">{{ $pedido->created_at->format('d/m/Y H:i') }}</div>
            <div class="sello">PAGADO</div>
        </td>
    </tr>
</table>

<hr class="divider">

<div class="client-wrap">
    <div class="client-box">
        <div class="client-tag">Cliente</div>
        <div class="client-name">{{ $pedido->nombre_cliente ?: 'Cliente' }}</div>
        <div class="client-meta">
            Tel.: +{{ $pedido->telefono }}<br>
            @if($pedido->requiere_factura && $pedido->ruc)RUC / C.I.: {{ $pedido->ruc }}<br>@endif
            Entrega: {{ $pedido->entrega_tipo === 'envio' ? 'Envío a ' . $pedido->direccion_entrega : 'Retiro en el local' }}<br>
            Forma de pago: {{ ucfirst((string) $pedido->forma_pago) }}@if($pedido->pagado_at) · Pagado el {{ $pedido->pagado_at->format('d/m/Y H:i') }}@endif
        </div>
    </div>
</div>

<div class="items-wrap">
    <table class="items-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th class="num">Cant.</th>
                <th class="num">Precio unit.</th>
                <th class="num">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->items as $it)
                <tr>
                    <td>
                        <div class="item-descr">{{ $it->descr }}</div>
                        <div class="item-code">{{ $it->item }}</div>
                    </td>
                    <td class="num">{{ rtrim(rtrim(number_format($it->cantidad, 3, ',', '.'), '0'), ',') }}</td>
                    <td class="num">Gs. {{ number_format($it->precio, 0, ',', '.') }}</td>
                    <td class="num">Gs. {{ number_format($it->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="totales-wrap">
    <table class="totales">
        <tr class="t-total">
            <td>TOTAL</td>
            <td style="text-align:right;">Gs. {{ number_format($pedido->total, 0, ',', '.') }}</td>
        </tr>
    </table>
</div>

<div class="footer">¡Gracias por tu compra en {{ $empresa->nombre }}! Comprobante de pedido pagado: documento informativo, no constituye factura.</div>
</body>
</html>
