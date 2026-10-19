<div style="background:white;padding:1rem;border-radius:.8rem;font-family:'Courier New',monospace;font-size:.75rem;line-height:1.4;color:#111;max-height:60vh;overflow-y:auto;">

    {{-- HEADER --}}
    <div style="text-align:center;padding-bottom:.5rem;border-bottom:1px solid #000;margin-bottom:.5rem;">
        @if($empresa->logo)
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(storage_path('app/public/' . $empresa->logo))) }}" style="max-width:50px;height:auto;margin-bottom:.3rem;" alt="Logo">
        @endif
        <div style="font-weight:bold;font-size:.9rem;">{{ strtoupper($empresa->nombre ?? 'EMPRESA') }}</div>
        <div style="font-size:.65rem;color:#666;">RUC: {{ $empresa->ruc ?? '---' }}</div>
    </div>

    {{-- SEPARADOR --}}
    <div style="border-bottom:1px dashed #000;margin:.3rem 0;"></div>

    {{-- INFO VENTA --}}
    <div style="text-align:center;margin-bottom:.5rem;font-size:.7rem;">
        <div><strong>TICKET DE VENTA</strong></div>
        <div>Nº {{ str_pad($invnr, 8, '0', STR_PAD_LEFT) }}</div>
        <div style="margin-top:.2rem;">{{ \Carbon\Carbon::parse($factura->fecha)->format('d/m/Y H:i') }}</div>
    </div>

    <div style="border-bottom:1px dashed #000;margin:.3rem 0;"></div>

    {{-- CLIENTE --}}
    @if($cliente)
        <div style="margin-bottom:.4rem;font-size:.7rem;">
            <div><strong>{{ strtoupper($cliente->custname ?? 'CLIENTE') }}</strong></div>
            @if($cliente->ruc)
                <div>RUC: {{ $cliente->ruc }}</div>
            @endif
            @if($cliente->addr)
                <div>{{ substr($cliente->addr, 0, 40) }}</div>
            @endif
        </div>
        <div style="border-bottom:1px dashed #000;margin:.3rem 0;"></div>
    @endif

    {{-- ITEMS --}}
    <table style="width:100%;margin-bottom:.5rem;border-collapse:collapse;font-size:.7rem;">
        <tbody>
            @foreach($detalles as $item)
                <tr style="border-bottom:1px dotted #ddd;">
                    <td style="padding:.2rem 0;text-align:left;">{{ substr($item->descr, 0, 28) }}</td>
                    <td style="padding:.2rem .2rem;text-align:right;">{{ number_format($item->qty, 0) }}</td>
                    <td style="padding:.2rem .2rem;text-align:right;">{{ number_format($item->precio, 0, ',', '.') }}</td>
                    <td style="padding:.2rem 0;text-align:right;"><strong>{{ number_format($item->qty * $item->precio, 0, ',', '.') }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="border-bottom:1px solid #000;margin:.3rem 0;"></div>

    {{-- TOTALES --}}
    <div style="margin-bottom:.5rem;font-size:.75rem;">
        <div style="display:flex;justify-content:space-between;margin-bottom:.2rem;">
            <span>SUBTOTAL:</span>
            <strong>Gs. {{ number_format($factura->total, 0, ',', '.') }}</strong>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:.2rem;">
            <span>MÉTODO PAGO:</span>
            <strong>
                @switch($factura->pago_tipo ?? 'efectivo')
                    @case('transferencia')
                        TRANSFERENCIA
                        @break
                    @case('tarjeta')
                        TARJETA
                        @break
                    @case('qr')
                        CÓDIGO QR
                        @break
                    @default
                        EFECTIVO
                @endswitch
            </strong>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.9rem;font-weight:bold;border-top:1px solid #000;padding-top:.2rem;">
            <span>TOTAL:</span>
            <span>Gs. {{ number_format($factura->total, 0, ',', '.') }}</span>
        </div>
    </div>

    <div style="border-bottom:1px dashed #000;margin:.3rem 0;"></div>

    {{-- FOOTER --}}
    <div style="text-align:center;font-size:.65rem;color:#666;">
        <div style="margin:.2rem 0;">¡Gracias por su compra!</div>
        <div style="margin:.2rem 0;">{{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</div>
    </div>

</div>
