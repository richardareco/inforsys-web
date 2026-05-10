<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Métodos de Pago</x-slot>

        <div style="display:flex;flex-direction:column;gap:16px;">
            @foreach($items as $item)
                @php $pct = round($item['pct'], 1); @endphp
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:7px;">
                        <div style="display:flex;align-items:center;gap:7px;">
                            <span style="width:11px;height:11px;border-radius:3px;background:{{ $item['color'] }};display:inline-block;flex-shrink:0;"></span>
                            <span style="font-size:0.85rem;font-weight:500;opacity:0.75;">{{ $item['label'] }}</span>
                        </div>
                        <span style="font-size:0.95rem;font-weight:800;letter-spacing:-0.3px;">
                            Gs. {{ number_format($item['amount'], 0, ',', '.') }}
                        </span>
                    </div>

                    <div style="background:rgba(128,128,128,0.15);border-radius:5px;height:20px;overflow:hidden;">
                        @if($item['amount'] > 0)
                            <div style="
                                background:{{ $item['color'] }};
                                width:{{ max($pct, 1.5) }}%;
                                height:100%;
                                border-radius:5px;
                                opacity:0.85;
                                display:flex;align-items:center;justify-content:flex-end;padding-right:7px;
                            ">
                                @if($pct >= 10)
                                    <span style="font-size:0.7rem;color:#fff;font-weight:700;">{{ $pct }}%</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if($pct > 0 && $pct < 10)
                        <span style="font-size:0.7rem;opacity:0.5;padding-left:4px;">{{ $pct }}%</span>
                    @endif
                </div>
            @endforeach

            @if($total > 0)
                <div style="border-top:1px solid rgba(128,128,128,0.2);padding-top:12px;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:0.8rem;font-weight:500;opacity:0.6;">Total cobrado</span>
                    <span style="font-size:1rem;font-weight:800;letter-spacing:-0.3px;">
                        Gs. {{ number_format($total, 0, ',', '.') }}
                    </span>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
