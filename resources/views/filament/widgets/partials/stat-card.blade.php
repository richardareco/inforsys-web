@php
    $change    = $card['change'] ?? null;
    $sub       = $card['sub'] ?? null;
    $hasUp     = $change === null || $change >= 0;
    $changeStr = $change !== null
        ? ($change >= 0 ? '+' : '') . number_format($change, 1, ',', '.') . '%'
        : null;
@endphp

<div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700" style="
    border-radius: 1rem;
    padding: 1rem 1.1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
">

    {{-- Top row: label + icon --}}
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:0.5rem;">

        <p style="
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6b7280;
            margin: 0;
            line-height: 1.4;
        ">{{ $card['label'] }}</p>

        <div style="
            background: {{ $card['iconBg'] }};
            border-radius: 0.6rem;
            padding: 0.45rem;
            flex-shrink: 0;
        ">
            <x-dynamic-component
                :component="$card['icon']"
                style="width:1.1rem; height:1.1rem; color:{{ $card['iconColor'] }}" />
        </div>

    </div>

    {{-- Amount --}}
    <p style="
        font-size: 1.2rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    " class="dark-value">{{ $card['value'] }}</p>

    {{-- Badge / sub --}}
    <div>
        @if ($changeStr !== null)
            <span style="
                display: inline-flex;
                align-items: center;
                gap: 2px;
                padding: 2px 8px;
                border-radius: 999px;
                font-size: 0.7rem;
                font-weight: 700;
                background: {{ $hasUp ? 'rgba(22,163,74,0.12)' : 'rgba(225,29,72,0.12)' }};
                color: {{ $hasUp ? '#16a34a' : '#e11d48' }};
            ">
                @if ($hasUp)
                    <svg style="width:0.6rem;height:0.6rem;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                    </svg>
                @else
                    <svg style="width:0.6rem;height:0.6rem;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                @endif
                {{ $changeStr }}
            </span>
        @elseif ($sub)
            <p style="font-size:0.68rem; color:#9ca3af; margin:0;">{{ $sub }}</p>
        @endif
    </div>

</div>

<style>
    .dark .dark-value { color: #f9fafb !important; }
    .dark [style*="background: rgba"] { /* dark mode card bg handled via rgba opacity */ }
</style>
