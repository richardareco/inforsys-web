@php
    $change    = $card['change'] ?? null;
    $sub       = $card['sub'] ?? null;
    $hasUp     = $change === null || $change >= 0;
    $changeStr = $change !== null
        ? ($change >= 0 ? '+' : '') . number_format($change, 1, ',', '.') . '%'
        : null;
    $bg        = $card['solidBg'] ?? $card['cardBg'] ?? '#6366f1';
@endphp

<div style="
    background: {{ $bg }};
    border-radius: 1rem;
    padding: 1.1rem 1.2rem 1rem;
    box-shadow: 0 4px 18px rgba(0,0,0,0.13);
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    position: relative;
    overflow: hidden;
    min-height: 105px;
">
    {{-- Círculos decorativos --}}
    <div style="position:absolute;top:-18px;right:-18px;width:88px;height:88px;border-radius:50%;background:rgba(255,255,255,0.1);pointer-events:none;"></div>
    <div style="position:absolute;bottom:-22px;right:16px;width:55px;height:55px;border-radius:50%;background:rgba(255,255,255,0.06);pointer-events:none;"></div>

    {{-- Label --}}
    <p style="font-size:0.62rem;font-weight:700;text-transform:uppercase;letter-spacing:0.09em;color:rgba(255,255,255,0.75);margin:0;line-height:1.3;">
        {{ $card['label'] }}
    </p>

    {{-- Valor principal --}}
    <p style="font-size:1.3rem;font-weight:800;color:#ffffff;margin:0;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        {{ $card['value'] }}
    </p>

    {{-- Footer: badge de cambio o subtexto + icono --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:0.35rem;">

        <div>
            @if ($changeStr !== null)
                <span style="
                    display:inline-flex;align-items:center;gap:3px;
                    padding:2px 8px;border-radius:999px;
                    font-size:0.62rem;font-weight:700;
                    background:rgba(255,255,255,0.2);color:#fff;
                ">
                    @if($hasUp)
                        <svg style="width:.55rem;height:.55rem" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                        </svg>
                    @else
                        <svg style="width:.55rem;height:.55rem" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    @endif
                    {{ $changeStr }}
                </span>
            @elseif ($sub)
                <p style="font-size:0.62rem;color:rgba(255,255,255,0.7);margin:0;">{{ $sub }}</p>
            @endif
        </div>

        <x-dynamic-component
            :component="$card['icon']"
            style="width:1.5rem;height:1.5rem;color:rgba(255,255,255,0.55);flex-shrink:0;"
        />

    </div>
</div>
