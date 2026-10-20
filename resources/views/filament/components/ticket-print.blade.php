@php
    $logoUrl = !empty($empresa?->logo) ? \Illuminate\Support\Facades\Storage::disk('public')->url($empresa->logo) : null;
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
    $total = (float) $factura->total;
    $iva10 = round($total / 11);
    $medioTxt = match ($medio) {
        'transferencia' => 'TRANSFERENCIA',
        'tarjeta' => 'TARJETA',
        'qr' => 'QR',
        'credito' => 'CRÉDITO',
        default => 'EFECTIVO',
    };
    $pagoMonto = $pago !== null ? (float) $pago : $total;
    $vuelto = $medio === 'efectivo' ? max(0, $pagoMonto - $total) : 0;
    $fecha = \Carbon\Carbon::parse($factura->fecha)->format('d/m/Y');
    $hora = $factura->hora ? substr((string) $factura->hora, 0, 5) : '';
    $ruc = $empresa->ruc ?? null;
    $direccion = $empresa->direccion ?? null;
    $telefono = $empresa->telefono ?? null;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket {{ str_pad($invnr, 6, '0', STR_PAD_LEFT) }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        @page { size: 80mm auto; margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #e5e7eb;
            font-family: 'IBM Plex Mono', 'Courier New', Courier, monospace;
            font-size: 11.5px;
            font-weight: 500;
            line-height: 1.4;
            color: #000;
        }
        .ticket {
            width: 80mm;
            margin: 16px auto;
            padding: 5mm 4mm 6mm;
            background: #fff;
            box-shadow: 0 6px 24px rgba(0,0,0,.18);
        }
        .center { text-align: center; }
        .logo { max-width: 56mm; max-height: 22mm; margin: 0 auto 2mm; display: block; }
        .brand {
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
            font-weight: 800;
            font-size: 21px;
            line-height: 1.15;
            letter-spacing: .3px;
            text-transform: uppercase;
            word-break: break-word;
        }
        .head-info { margin-top: 1.5mm; font-size: 11px; }
        .dash { border: 0; border-top: 1px dashed #000; margin: 2.2mm 0; }
        .title {
            font-family: 'Poppins', Arial, sans-serif;
            font-weight: 700;
            font-size: 12.5px;
            text-align: center;
            letter-spacing: 2px;
            margin: 1mm 0 1.8mm;
        }
        .row { display: flex; justify-content: space-between; gap: 6px; }
        table { width: 100%; border-collapse: collapse; table-layout: auto; }
        th { text-align: left; font-weight: 700; padding: 0 0 1.2mm 4px; font-size: 11px; }
        th:first-child, td:first-child { padding-left: 0; }
        th.r, td.r { text-align: right; white-space: nowrap; }
        td { vertical-align: top; padding: .5mm 0 .5mm 4px; }
        td.desc { word-break: break-word; width: 100%; }
        .totales .row { margin: .8mm 0; }
        .total-line { font-weight: 700; font-size: 15px; }
        th { font-weight: 700; }
        .foot { font-family: 'Poppins', Arial, sans-serif; font-weight: 600; font-size: 11.5px; }
        .foot { text-align: center; margin-top: 1mm; }
        .btns { text-align: center; margin: 12px 0 24px; font-family: Arial, sans-serif; }
        .btns button { padding: 9px 18px; border: 0; border-radius: 8px; background: #4f46e5; color: #fff; font-weight: 700; cursor: pointer; margin: 0 4px; }
        .btns button.sec { background: #fff; color: #374151; border: 1px solid #d1d5db; }
        @media print {
            body { background: #fff; }
            .ticket { margin: 0; box-shadow: none; width: 80mm; }
            .btns { display: none; }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="center">
            @if($logoUrl)
                <img class="logo" src="{{ $logoUrl }}" alt="Logo">
            @endif
            <div class="brand">{{ $empresa->nombre ?? 'Inforsys' }}</div>
            <div class="head-info">
                @if($ruc)<div>RUC: {{ $ruc }}</div>@endif
                @if($telefono)<div>Tel: {{ $telefono }}</div>@endif
                @if($direccion)<div>{{ $direccion }}</div>@endif
            </div>
        </div>

        <hr class="dash">
        <div class="title">TICKET DE VENTA</div>
        @if(!empty($reimpresion))
            <div style="text-align:center;font-weight:700;letter-spacing:1.5px;font-size:11px;margin:-.5mm 0 1.5mm;border:1px solid #000;padding:.6mm 0;">*** REIMPRESIÓN ***</div>
            <div style="text-align:center;font-size:10px;margin-bottom:1.5mm;">Impreso el {{ now()->format('d/m/Y H:i') }}</div>
        @endif

        <div class="row"><span>Fecha: {{ $fecha }}</span><span>Hora: {{ $hora }}</span></div>
        <div class="row"><span>Caja: {{ $factura->cajanr ?? '-' }}</span><span>Ticket: {{ str_pad($invnr, 6, '0', STR_PAD_LEFT) }}</span></div>
        <div>Cajero: {{ strtoupper($cajero->pername ?? '-') }}</div>
        <div>Cliente: {{ strtoupper($cliente->custname ?? 'MOSTRADOR') }}</div>
        @if(!empty($cliente?->ruc))<div>RUC: {{ $cliente->ruc }}</div>@endif

        <hr class="dash">

        <table>
            <thead>
                <tr>
                    <th>COD</th>
                    <th>DESCRIPCION</th>
                    <th class="r">CANT</th>
                    <th class="r">PRECIO</th>
                    <th class="r">SUBT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detalles as $d)
                    <tr>
                        <td>{{ ltrim((string) $d->item, '0') ?: $d->item }}</td>
                        <td class="desc">{{ mb_substr((string) $d->descr, 0, 22) }}</td>
                        <td class="r">{{ rtrim(rtrim(number_format((float) $d->qty, 2, ',', '.'), '0'), ',') }}</td>
                        <td class="r">{{ $fmt($d->precio) }}</td>
                        <td class="r">{{ $fmt($d->qty * $d->precio) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <hr class="dash">

        <div class="totales">
            <div class="row"><span>SUBTOTAL:</span><span>{{ $fmt($total) }}</span></div>
            <div class="row"><span>IVA 10% (incl.):</span><span>{{ $fmt($iva10) }}</span></div>
            <div class="row total-line"><span>TOTAL GS:</span><span>{{ $fmt($total) }}</span></div>
            <div class="row"><span>PAGO: {{ $medioTxt }}</span><span>{{ $medio === 'efectivo' ? $fmt($pagoMonto) : '' }}</span></div>
            @if($medio === 'efectivo' && $pago !== null)
                <div class="row"><span>VUELTO:</span><span>{{ $fmt($vuelto) }}</span></div>
            @endif
        </div>

        <hr class="dash">

        <div class="foot">
            <div>¡Gracias por su compra!</div>
            <div>{{ $empresa->nombre ?? 'Inforsys' }}</div>
        </div>
    </div>

    <div class="btns">
        <button type="button" onclick="window.print()">Imprimir</button>
        <button type="button" class="sec" onclick="window.close()">Cerrar</button>
    </div>

    @unless(request()->boolean('preview'))
    <script>
        window.addEventListener('load', function () {
            var fonts = (document.fonts && document.fonts.ready) ? document.fonts.ready : Promise.resolve();
            var limite = new Promise(function (r) { setTimeout(r, 1500); });
            Promise.race([fonts, limite]).then(function () {
                setTimeout(function () { window.print(); }, 150);
            });
        });
    </script>
    @endunless
</body>
</html>
