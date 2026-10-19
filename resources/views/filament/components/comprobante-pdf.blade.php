<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Comprobante {{ $invnr }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #111;
        }

        .ticket {
            width: 80mm;
            margin: 0 auto;
            padding: 8px;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .logo {
            width: 100%;
            max-width: 60px;
            height: auto;
            margin: 0 auto 4px;
        }

        .empresa-nombre {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 4px 0;
        }

        .empresa-ruc {
            font-size: 9px;
            color: #666;
            margin: 2px 0;
        }

        .separador {
            border-top: 1px solid #000;
            margin: 6px 0;
        }

        .info-venta {
            font-size: 9px;
            margin-bottom: 8px;
            text-align: left;
        }

        .info-venta div {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }

        .label {
            font-weight: bold;
        }

        .cliente-info {
            background: #f5f5f5;
            padding: 4px;
            border-radius: 2px;
            margin-bottom: 6px;
            font-size: 9px;
        }

        .cliente-nombre {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .tabla-productos {
            width: 100%;
            margin: 6px 0;
            border-collapse: collapse;
            font-size: 9px;
        }

        .tabla-productos th {
            border-bottom: 1px solid #000;
            padding: 3px 2px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
        }

        .tabla-productos td {
            padding: 3px 2px;
            border-bottom: 1px dotted #ddd;
        }

        .tabla-productos .col-desc {
            flex: 1;
            text-align: left;
        }

        .tabla-productos .col-qty {
            width: 35px;
            text-align: center;
        }

        .tabla-productos .col-precio {
            width: 45px;
            text-align: right;
        }

        .tabla-productos .col-subtotal {
            width: 50px;
            text-align: right;
        }

        .item-desc {
            font-size: 9px;
        }

        .item-detail {
            font-size: 8px;
            color: #666;
        }

        .totales {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 6px 0;
            margin: 6px 0;
            font-size: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }

        .total-label {
            font-weight: bold;
        }

        .total-monto {
            font-weight: bold;
            text-align: right;
        }

        .total-final {
            font-size: 14px;
            font-weight: bold;
            margin-top: 4px;
        }

        .pago-info {
            background: #f5f5f5;
            padding: 4px;
            border-radius: 2px;
            margin: 6px 0;
            font-size: 9px;
            text-align: center;
        }

        .footer {
            text-align: center;
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px solid #000;
            font-size: 8px;
            color: #666;
        }

        .footer-text {
            margin: 2px 0;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .ticket {
                width: 80mm;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="ticket">
        {{-- CABECERA --}}
        <div class="header">
            @if($empresa->logo)
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(storage_path('app/public/' . $empresa->logo))) }}" class="logo" alt="Logo">
            @else
                <div style="width: 60px; height: 60px; margin: 0 auto; background: #e5e5e5; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 10px; color: #999;">Sin Logo</div>
            @endif
            <div class="empresa-nombre">{{ strtoupper($empresa->nombre ?? 'EMPRESA') }}</div>
            <div class="empresa-ruc">RUC: {{ $empresa->ruc ?? '---' }}</div>
        </div>

        {{-- NÚMERO Y FECHA --}}
        <div class="info-venta">
            <div>
                <span class="label">COMPROBANTE Nº:</span>
                <span>{{ str_pad($invnr, 8, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div>
                <span class="label">FECHA:</span>
                <span>{{ \Carbon\Carbon::parse($factura->fecha)->format('d/m/Y H:i') }}</span>
            </div>
            @if($cajero)
                <div>
                    <span class="label">CAJERO:</span>
                    <span>{{ $cajero->name ?? '---' }}</span>
                </div>
            @endif
        </div>

        <div class="separador"></div>

        {{-- CLIENTE --}}
        @if($cliente)
            <div class="cliente-info">
                <div class="cliente-nombre">{{ strtoupper($cliente->nombre ?? 'CLIENTE') }}</div>
                @if($cliente->ruc)
                    <div>RUC: {{ $cliente->ruc }}</div>
                @endif
                @if($cliente->direccion)
                    <div>{{ $cliente->direccion }}</div>
                @endif
            </div>
        @endif

        {{-- PRODUCTOS --}}
        <table class="tabla-productos">
            <thead>
                <tr>
                    <th style="width: 100px;">DESCRIPCIÓN</th>
                    <th style="width: 30px; text-align: center;">QTY</th>
                    <th style="width: 40px; text-align: right;">P.UNIT</th>
                    <th style="width: 50px; text-align: right;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detalles as $detalle)
                    <tr>
                        <td class="item-desc">{{ substr($detalle->descr, 0, 35) }}</td>
                        <td style="text-align: center;">{{ number_format($detalle->qty, 0) }}</td>
                        <td style="text-align: right;">{{ number_format($detalle->precio, 0, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($detalle->qty * $detalle->precio, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- TOTALES --}}
        <div class="totales">
            <div class="total-row">
                <span class="total-label">SUBTOTAL:</span>
                <span class="total-monto">Gs. {{ number_format($factura->total, 0, ',', '.') }}</span>
            </div>
            {{-- IVA si aplica --}}
            @php
                $iva = 0;
                if(isset($factura->iva)) {
                    $iva = $factura->iva;
                }
            @endphp
            @if($iva > 0)
                <div class="total-row">
                    <span class="total-label">IVA (10%):</span>
                    <span class="total-monto">Gs. {{ number_format($iva, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="total-row total-final">
                <span>TOTAL:</span>
                <span>Gs. {{ number_format($factura->total, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- MÉTODO DE PAGO --}}
        <div class="pago-info">
            <div style="font-weight: bold; margin-bottom: 2px;">FORMA DE PAGO</div>
            <div>
                @switch($factura->pago_tipo ?? 'efectivo')
                    @case('transferencia')
                        Transferencia Bancaria
                        @break
                    @case('tarjeta')
                        Tarjeta de Crédito/Débito
                        @break
                    @case('qr')
                        Código QR
                        @break
                    @default
                        Efectivo
                @endswitch
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="footer">
            <div class="footer-text">¡Gracias por su compra!</div>
            <div class="footer-text">Conserve el comprobante</div>
            <div class="footer-text">{{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</div>
        </div>
    </div>
</body>
</html>
