@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
    $logoUrl = !empty($empresa?->logo) ? \Illuminate\Support\Facades\Storage::disk('public')->url($empresa->logo) : null;
    $cuadra = abs($d['diferencia']) < 1;
    $estadoTxt = $cuadra ? 'CAJA CUADRADA' : ($d['diferencia'] > 0 ? 'FALTAN POR REGISTRAR EN CAJA' : 'CAJA SUPERA A LAS VENTAS');
    $estadoCls = $cuadra ? 'ok' : ($d['diferencia'] > 0 ? 'bad' : 'warn');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cierre de Caja {{ \Carbon\Carbon::parse($fecha)->format('d-m-Y') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        @page { size: A4 portrait; margin: 14mm 12mm 16mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --azul: #0047ab; --azul2: #0a6cdc; --tinta: #0f172a; --gris: #64748b; --linea: #e2e8f0; }
        body { background: #e5e7eb; font-family: 'Inter', Arial, sans-serif; font-size: 11px; color: var(--tinta); line-height: 1.4; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .page { width: 210mm; min-height: 297mm; margin: 14px auto; background: #fff; padding: 14mm 12mm 16mm; box-shadow: 0 8px 30px rgba(0,0,0,.18); position: relative; }

        .head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; padding-bottom: 12px; border-bottom: 3px solid var(--azul); }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand img { max-height: 16mm; max-width: 40mm; }
        .brand-name { font-family: 'Poppins', Arial, sans-serif; font-weight: 800; font-size: 19px; text-transform: uppercase; color: var(--azul); letter-spacing: .3px; line-height: 1.1; }
        .brand-sub { font-size: 9.5px; color: var(--gris); margin-top: 2px; }
        .doc { text-align: right; }
        .doc-title { font-family: 'Poppins', Arial, sans-serif; font-weight: 800; font-size: 20px; letter-spacing: 1.5px; color: var(--tinta); }
        .doc-date { font-size: 12px; font-weight: 700; color: var(--azul); margin-top: 3px; }
        .doc-meta { font-size: 9px; color: var(--gris); margin-top: 3px; }

        .kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin: 14px 0 12px; }
        .kpi { border: 1px solid var(--linea); border-radius: 8px; padding: 9px 10px; }
        .kpi.hero { background: linear-gradient(135deg, #0a3fa8, #0047ab 50%, #0284c7); color: #fff; border: none; }
        .kpi-l { font-size: 8px; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; color: var(--gris); }
        .kpi.hero .kpi-l { color: rgba(255,255,255,.8); }
        .kpi-v { font-family: 'Poppins', Arial, sans-serif; font-weight: 800; font-size: 14.5px; margin-top: 3px; letter-spacing: -.01em; }
        .kpi-s { font-size: 8.5px; color: var(--gris); margin-top: 1px; }
        .kpi.hero .kpi-s { color: rgba(255,255,255,.85); }

        .two { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px; }
        .box { border: 1px solid var(--linea); border-radius: 8px; padding: 10px 12px; }
        .box-t { font-family: 'Poppins', Arial, sans-serif; font-weight: 700; font-size: 11px; color: var(--azul); text-transform: uppercase; letter-spacing: .06em; padding-bottom: 6px; margin-bottom: 6px; border-bottom: 1px solid var(--linea); }

        .met { display: grid; grid-template-columns: 1fr auto; gap: 2px 8px; padding: 5px 0; align-items: center; }
        .met-n { display: flex; align-items: center; gap: 6px; font-weight: 600; }
        .dot { width: 8px; height: 8px; border-radius: 9999px; display: inline-block; }
        .met-m { font-weight: 800; text-align: right; }
        .bar { grid-column: 1 / -1; height: 5px; border-radius: 9999px; background: #eef2f7; overflow: hidden; }
        .bar > i { display: block; height: 100%; border-radius: 9999px; }
        .pct { font-weight: 500; color: var(--gris); font-size: 9px; margin-left: 4px; }

        .row { display: flex; justify-content: space-between; padding: 4px 0; }
        .row.tot { border-top: 1px dashed #94a3b8; margin-top: 4px; padding-top: 7px; font-size: 12px; }
        .row b { font-weight: 800; }
        .estado { margin-top: 8px; text-align: center; padding: 7px; border-radius: 6px; font-weight: 800; font-size: 10px; letter-spacing: .06em; }
        .estado.ok { background: #dcfce7; color: #15803d; }
        .estado.bad { background: #fee2e2; color: #b91c1c; }
        .estado.warn { background: #fef3c7; color: #b45309; }

        table { width: 100%; border-collapse: collapse; }
        thead { display: table-header-group; }
        th { text-align: left; font-size: 8px; letter-spacing: .08em; text-transform: uppercase; color: #fff; background: var(--azul); padding: 6px 8px; font-weight: 700; }
        th:first-child { border-radius: 6px 0 0 6px; } th:last-child { border-radius: 0 6px 6px 0; }
        td { padding: 5px 8px; border-bottom: 1px solid var(--linea); }
        tbody tr:nth-child(even) td { background: #f8fafc; }
        tr { break-inside: avoid; }
        .r { text-align: right; }
        .tag { display: inline-block; padding: 1px 7px; border-radius: 9999px; background: #eef2ff; color: #4338ca; font-weight: 700; font-size: 9px; }
        .tag.red { background: #fee2e2; color: #b91c1c; }
        tfoot td { font-weight: 800; border-top: 2px solid var(--azul); border-bottom: none; background: #fff !important; padding-top: 7px; }

        .firmas { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 28px; margin-top: 34px; }
        .firma { text-align: center; }
        .firma-l { border-top: 1px solid #334155; padding-top: 4px; font-size: 9.5px; font-weight: 600; color: #334155; }
        .firma-s { font-size: 8.5px; color: var(--gris); }

        .foot { margin-top: 18px; padding-top: 7px; border-top: 1px solid var(--linea); display: flex; justify-content: space-between; font-size: 8.5px; color: var(--gris); }
        .nota { margin-top: 6px; font-size: 8.5px; color: var(--gris); }

        .btns { text-align: center; margin: 4px 0 26px; font-family: Inter, Arial, sans-serif; }
        .btns button { padding: 10px 20px; border: 0; border-radius: 9px; background: #4f46e5; color: #fff; font-weight: 700; cursor: pointer; margin: 0 4px; font-size: 13px; }
        .btns button.sec { background: #fff; color: #374151; border: 1px solid #d1d5db; }

        @media print {
            body { background: #fff; }
            .page { margin: 0; box-shadow: none; width: auto; min-height: 0; padding: 0; }
            .btns { display: none; }
        }
    </style>
</head>
<body>
<div class="page">

    <div class="head">
        <div class="brand">
            @if($logoUrl)<img src="{{ $logoUrl }}" alt="Logo">@endif
            <div>
                <div class="brand-name">{{ $empresa->nombre ?? 'Inforsys' }}</div>
                <div class="brand-sub">Sistema de Gestión y Punto de Venta</div>
            </div>
        </div>
        <div class="doc">
            <div class="doc-title">CIERRE DE CAJA</div>
            <div class="doc-date">{{ $d['fechaTexto'] }}</div>
            <div class="doc-meta">Generado el {{ $generado }} por {{ $usuario->name ?? '—' }}</div>
        </div>
    </div>

    <div class="kpis">
        <div class="kpi hero">
            <div class="kpi-l">Total cobrado en caja</div>
            <div class="kpi-v">Gs. {{ $fmt($d['totalCobrado']) }}</div>
            <div class="kpi-s">{{ $d['movimientos'] }} {{ $d['movimientos'] === 1 ? 'movimiento' : 'movimientos' }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-l">Ventas del día</div>
            <div class="kpi-v">Gs. {{ $fmt($d['ventasTotal']) }}</div>
            <div class="kpi-s">{{ $d['ventasCantidad'] }} {{ $d['ventasCantidad'] === 1 ? 'venta' : 'ventas' }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-l">Ventas al contado</div>
            <div class="kpi-v">Gs. {{ $fmt($d['ventasContado']) }}</div>
            <div class="kpi-s">Deben estar cobradas</div>
        </div>
        <div class="kpi">
            <div class="kpi-l">Ventas a crédito</div>
            <div class="kpi-v">Gs. {{ $fmt($d['ventasCredito']) }}</div>
            <div class="kpi-s">No ingresan a caja</div>
        </div>
    </div>

    <div class="two">
        <div class="box">
            <div class="box-t">Cobros por forma de pago</div>
            @foreach($d['metodos'] as $m)
                <div class="met">
                    <div class="met-n"><span class="dot" style="background:{{ $m['color'] }};"></span>{{ $m['label'] }}<span class="pct">{{ number_format($m['pct'], 1, ',', '.') }}%</span></div>
                    <div class="met-m">Gs. {{ $fmt($m['monto']) }}</div>
                    <div class="bar"><i style="width:{{ number_format($m['pct'], 1, '.', '') }}%;background:{{ $m['color'] }};"></i></div>
                </div>
            @endforeach
            <div class="row tot"><span>Total cobrado</span><b>Gs. {{ $fmt($d['totalCobrado']) }}</b></div>
        </div>

        <div class="box">
            <div class="box-t">Caja contra ventas</div>
            <div class="row"><span>Ventas al contado</span><b>Gs. {{ $fmt($d['ventasContado']) }}</b></div>
            <div class="row"><span>Cobrado en caja</span><b>Gs. {{ $fmt($d['totalCobrado']) }}</b></div>
            <div class="row tot"><span>Diferencia</span><b style="color: {{ $cuadra ? '#15803d' : '#b91c1c' }};">{{ $d['diferencia'] > 0 ? '+' : '' }}Gs. {{ $fmt($d['diferencia']) }}</b></div>
            <div class="estado {{ $estadoCls }}">{{ $estadoTxt }}</div>
            @if($d['sinCobro'] > 0)
                <div class="nota" style="color:#b91c1c;font-weight:600;">{{ $d['sinCobro'] }} {{ $d['sinCobro'] === 1 ? 'venta sin cobro' : 'ventas sin cobro' }} en caja (ver detalle).</div>
            @endif
            @if($d['salidas'] > 0)
                <div class="nota">Devoluciones / salidas no incluidas: Gs. {{ $fmt($d['salidas']) }}</div>
            @endif
        </div>
    </div>

    <div class="box" style="padding:10px 0 4px;border:none;">
        <div class="box-t" style="margin:0 0 8px;padding:0 0 6px;">Detalle de ventas del día</div>
        <table>
            <thead>
                <tr><th>Nro. venta</th><th>Hora</th><th>Cliente</th><th>Forma de pago</th><th class="r">Total (Gs.)</th></tr>
            </thead>
            <tbody>
                @forelse($d['detalle'] as $r)
                    <tr>
                        <td><b>{{ $r->invnr }}</b></td>
                        <td>{{ $r->hora ? substr((string) $r->hora, 0, 5) : '—' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($r->custname ?? '—', 34) }}</td>
                        <td><span class="tag {{ $r->metodo === 'Sin cobro en caja' ? 'red' : '' }}">{{ $r->metodo }}</span></td>
                        <td class="r"><b>{{ $fmt($r->total) }}</b></td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:18px;">No hay ventas registradas en esta fecha.</td></tr>
                @endforelse
            </tbody>
            @if(count($d['detalle']) > 0)
            <tfoot>
                <tr><td colspan="4">TOTAL VENTAS ({{ $d['ventasCantidad'] }})</td><td class="r">{{ $fmt($d['ventasTotal']) }}</td></tr>
            </tfoot>
            @endif
        </table>
    </div>

    <div class="firmas">
        <div class="firma"><div class="firma-l">Cajero/a</div><div class="firma-s">Firma y aclaración</div></div>
        <div class="firma"><div class="firma-l">Supervisor/a</div><div class="firma-s">Firma y aclaración</div></div>
        <div class="firma"><div class="firma-l">Administración</div><div class="firma-s">Firma y aclaración</div></div>
    </div>

    <div class="foot">
        <span>{{ $empresa->nombre ?? 'Inforsys' }} · Cierre de caja del {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</span>
        <span>Informe generado automáticamente por Inforsys</span>
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
        Promise.race([fonts, limite]).then(function () { setTimeout(function () { window.print(); }, 150); });
    });
</script>
@endunless
</body>
</html>
