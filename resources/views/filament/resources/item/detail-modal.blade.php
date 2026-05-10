<div style="display:flex; flex-direction:column; gap:1.25rem; padding:0.25rem 0">

    {{-- Info principal --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">

        <div style="background:rgba(124,58,237,0.07); border-radius:0.75rem; padding:0.85rem 1rem;">
            <p style="font-size:0.65rem; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:#6b7280; margin:0 0 0.25rem">Referencia / Código</p>
            <p style="font-size:0.9rem; font-weight:600; color:#111827; margin:0;">{{ $item->scode ?: '—' }} / {{ $item->item }}</p>
        </div>

        <div style="background:rgba(59,130,246,0.07); border-radius:0.75rem; padding:0.85rem 1rem;">
            <p style="font-size:0.65rem; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:#6b7280; margin:0 0 0.25rem">Código de Barra</p>
            <p style="font-size:0.9rem; font-weight:600; color:#111827; margin:0;">{{ $item->scode1 ?: '—' }}</p>
        </div>

        <div style="grid-column:1/-1; background:rgba(234,88,12,0.06); border-radius:0.75rem; padding:0.85rem 1rem;">
            <p style="font-size:0.65rem; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:#6b7280; margin:0 0 0.25rem">Observación</p>
            <p style="font-size:0.85rem; color:#374151; margin:0;">{{ $item->obs ?: '—' }}</p>
        </div>

        @if($proveedor)
        <div style="grid-column:1/-1; background:rgba(13,148,136,0.07); border-radius:0.75rem; padding:0.85rem 1rem;">
            <p style="font-size:0.65rem; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:#6b7280; margin:0 0 0.25rem">Proveedor</p>
            <p style="font-size:0.9rem; font-weight:600; color:#111827; margin:0 0 0.2rem;">{{ $proveedor->suppname }}</p>
            @if($proveedor->telef1 || $proveedor->celular1)
                <p style="font-size:0.78rem; color:#6b7280; margin:0;">
                    {{ $proveedor->telef1 ?: '' }}{{ ($proveedor->telef1 && $proveedor->celular1) ? ' · ' : '' }}{{ $proveedor->celular1 ?: '' }}
                </p>
            @endif
        </div>
        @endif

    </div>

    {{-- Precios --}}
    <div>
        <p style="font-size:0.7rem; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:#6b7280; margin:0 0 0.5rem;">Precios</p>
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:0.5rem;">
            @foreach([['Precio 1','precio1','#2563eb'],['Precio 2','precio2','#7c3aed'],['Precio 3','precio3','#0d9488']] as [$lbl,$field,$clr])
            <div style="border-radius:0.75rem; padding:0.7rem 0.85rem; text-align:center; background:rgba(0,0,0,0.03); border:1px solid rgba(0,0,0,0.06);">
                <p style="font-size:0.65rem; font-weight:600; text-transform:uppercase; color:#9ca3af; margin:0 0 0.2rem;">{{ $lbl }}</p>
                <p style="font-size:0.95rem; font-weight:700; color:{{ $clr }}; margin:0;">
                    Gs. {{ number_format($item->$field, 0, ',', '.') }}
                </p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Depósitos --}}
    <div>
        <p style="font-size:0.7rem; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:#6b7280; margin:0 0 0.5rem;">
            Stock por Depósito
        </p>

        @if($depositos->isEmpty())
            <div style="text-align:center; padding:1rem; color:#9ca3af; font-size:0.85rem;">
                Sin stock en depósitos
            </div>
        @else
            <div style="border-radius:0.75rem; overflow:hidden; border:1px solid rgba(0,0,0,0.07);">
                <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                    <thead>
                        <tr style="background:rgba(0,0,0,0.04);">
                            <th style="text-align:left; padding:0.5rem 0.75rem; font-size:0.65rem; font-weight:600; text-transform:uppercase; color:#6b7280;">Depósito</th>
                            <th style="text-align:left; padding:0.5rem 0.75rem; font-size:0.65rem; font-weight:600; text-transform:uppercase; color:#6b7280;">Código</th>
                            <th style="text-align:right; padding:0.5rem 0.75rem; font-size:0.65rem; font-weight:600; text-transform:uppercase; color:#6b7280;">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($depositos as $i => $dep)
                        <tr style="border-top:1px solid rgba(0,0,0,0.05); {{ $i % 2 === 1 ? 'background:rgba(0,0,0,0.02)' : '' }}">
                            <td style="padding:0.55rem 0.75rem; font-weight:500; color:#111827;">{{ $dep->depo_nombre }}</td>
                            <td style="padding:0.55rem 0.75rem; color:#6b7280; font-size:0.8rem;">{{ $dep->depo }}</td>
                            <td style="padding:0.55rem 0.75rem; text-align:right;">
                                <span style="font-weight:700; color:{{ $dep->qty > 5 ? '#16a34a' : '#d97706' }};">
                                    {{ number_format($dep->qty, 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top:2px solid rgba(0,0,0,0.08); background:rgba(0,0,0,0.03);">
                            <td colspan="2" style="padding:0.55rem 0.75rem; font-size:0.75rem; font-weight:600; color:#6b7280;">TOTAL</td>
                            <td style="padding:0.55rem 0.75rem; text-align:right; font-weight:700; color:#111827;">
                                {{ number_format($depositos->sum('qty'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

</div>
