<x-filament-widgets::widget>
    <x-filament::section>

        {{-- Header --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <div>
                <p style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;opacity:0.5;margin:0 0 2px;">Meta de Ventas</p>
                <p style="font-size:0.95rem;font-weight:700;margin:0;text-transform:capitalize;">{{ $mesLabel }}</p>
            </div>
            <select wire:model.live="periodo"
                style="font-size:0.8rem;padding:4px 10px;border-radius:6px;border:1px solid rgba(128,128,128,0.3);background:transparent;cursor:pointer;outline:none;">
                <option value="mes">Mes</option>
                <option value="dia">Día</option>
            </select>
        </div>

        {{-- Gauge: x-data con $wire.entangle para reactividad real --}}
        <div
            x-data="{
                pct: $wire.entangle('percentage'),
                init() { this.$nextTick(() => this.draw()); },
                draw() {
                    var canvas = document.getElementById('velocimetro-gauge-canvas');
                    if (!canvas) return;
                    var ctx = canvas.getContext('2d');
                    var cx=140, cy=148, outerR=115, innerR=80;
                    ctx.clearRect(0,0,canvas.width,canvas.height);

                    ctx.beginPath();
                    ctx.arc(cx,cy,outerR+5,Math.PI,0,false);
                    ctx.arc(cx,cy,innerR-5,0,Math.PI,true);
                    ctx.closePath();
                    ctx.fillStyle='rgba(100,116,139,0.15)';
                    ctx.fill();

                    [{color:'#ef4444',from:0,to:0.4},{color:'#f59e0b',from:0.4,to:0.7},{color:'#22c55e',from:0.7,to:1.0}].forEach(z=>{
                        var a1=Math.PI+z.from*Math.PI, a2=Math.PI+z.to*Math.PI;
                        ctx.beginPath();ctx.arc(cx,cy,outerR,a1,a2,false);ctx.arc(cx,cy,innerR,a2,a1,true);ctx.closePath();
                        ctx.fillStyle=z.color+'44';ctx.fill();
                        ctx.strokeStyle=z.color+'aa';ctx.lineWidth=1.5;ctx.stroke();
                    });

                    if(this.pct>0){
                        var ea=Math.PI+(this.pct/100)*Math.PI;
                        var fc=this.pct>=70?'#22c55e':(this.pct>=40?'#f59e0b':'#ef4444');
                        ctx.beginPath();ctx.arc(cx,cy,outerR,Math.PI,ea,false);ctx.arc(cx,cy,innerR,ea,Math.PI,true);ctx.closePath();
                        ctx.fillStyle=fc+'cc';ctx.fill();
                    }

                    for(var i=0;i<=10;i++){
                        var ta=Math.PI+(i/10)*Math.PI,main=(i%5===0);
                        ctx.beginPath();
                        ctx.moveTo(cx+(outerR+(main?7:4))*Math.cos(ta),cy+(outerR+(main?7:4))*Math.sin(ta));
                        ctx.lineTo(cx+(outerR+1)*Math.cos(ta),cy+(outerR+1)*Math.sin(ta));
                        ctx.strokeStyle='rgba(100,116,139,0.6)';ctx.lineWidth=main?2:1;ctx.stroke();
                    }

                    ctx.font='600 11px Inter,sans-serif';ctx.fillStyle='rgba(100,116,139,0.8)';
                    ctx.textAlign='center';ctx.textBaseline='middle';
                    ctx.fillText('0%',cx-outerR-14,cy+14);
                    ctx.fillText('50%',cx,cy-outerR-16);
                    ctx.fillText('100%',cx+outerR+16,cy+14);

                    var na=Math.PI+(this.pct/100)*Math.PI;
                    ctx.beginPath();
                    ctx.moveTo(cx-Math.cos(na)*14,cy-Math.sin(na)*14);
                    ctx.lineTo(cx+Math.cos(na)*(innerR-8),cy+Math.sin(na)*(innerR-8));
                    ctx.strokeStyle='#64748b';ctx.lineWidth=2.5;ctx.lineCap='round';ctx.stroke();

                    ctx.beginPath();ctx.arc(cx,cy,9,0,Math.PI*2);
                    ctx.fillStyle='rgba(100,116,139,0.9)';ctx.fill();
                    ctx.strokeStyle='rgba(255,255,255,0.4)';ctx.lineWidth=2;ctx.stroke();
                }
            }"
            x-effect="draw()"
            style="display:flex;flex-direction:column;align-items:center;gap:10px;"
        >
            {{-- wire:ignore prevents Livewire from clearing the canvas on DOM morph --}}
            <div wire:ignore style="width:100%;max-width:280px;">
                <canvas id="velocimetro-gauge-canvas" width="280" height="158"
                    style="width:100%;display:block;"></canvas>
            </div>

            @if($meta === null)
                <div style="text-align:center;">
                    <p style="font-size:0.8rem;opacity:0.5;margin:0 0 4px;">Sin meta configurada</p>
                    <p style="font-size:0.85rem;font-weight:600;margin:0;">
                        Ventas: Gs. {{ number_format($actual, 0, ',', '.') }}
                    </p>
                </div>
            @else
                @php
                    $statusLabel = $percentage >= 70 ? 'En Meta' : ($percentage >= 40 ? 'Cerca' : 'Bajo');
                    $statusColor = $percentage >= 70 ? '#22c55e' : ($percentage >= 40 ? '#f59e0b' : '#ef4444');
                    $metaLabel   = $periodo === 'dia' ? 'Meta del día' : 'Meta del mes';
                @endphp
                <div style="text-align:center;">
                    <p style="font-size:1.6rem;font-weight:800;margin:0;color:{{ $statusColor }};">
                        {{ $percentage }}%
                    </p>
                    <p style="font-size:0.8rem;font-weight:600;color:{{ $statusColor }};margin:2px 0 6px;">
                        {{ $statusLabel }}
                    </p>
                    <p style="font-size:0.75rem;opacity:0.6;margin:0;">
                        Gs. {{ number_format($actual, 0, ',', '.') }}
                        <span style="opacity:0.7;"> / {{ number_format($meta, 0, ',', '.') }}</span>
                    </p>
                    <p style="font-size:0.7rem;opacity:0.4;margin:3px 0 0;">{{ $metaLabel }}</p>
                </div>
            @endif
        </div>

    </x-filament::section>
</x-filament-widgets::widget>
