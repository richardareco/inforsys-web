<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-trophy class="h-5 w-5 text-amber-400" />
                <span>Top 5 Vendedores</span>
            </div>
        </x-slot>

        @php
            $rankStyles = [
                0 => ['bg' => '#f59e0b', 'ring' => 'ring-amber-300'],
                1 => ['bg' => '#9ca3af', 'ring' => 'ring-gray-300'],
                2 => ['bg' => '#b45309', 'ring' => 'ring-amber-600'],
            ];
            $avatarColors = ['#3b82f6','#8b5cf6','#10b981','#f97316','#f43f5e'];
        @endphp

        @if ($vendedores->isEmpty())
            <div class="py-10 text-center">
                <x-heroicon-o-inbox class="mx-auto h-10 w-10 text-gray-300" />
                <p class="mt-2 text-sm text-gray-400">Sin datos para el período</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-5">
                @foreach ($vendedores as $i => $v)
                    @php
                        $comision    = (float) $v->total_ventas * (float) $v->porc_comision / 100;
                        $rankStyle   = $rankStyles[$i] ?? null;
                        $avatarColor = $avatarColors[$i % count($avatarColors)];
                        $initials    = strtoupper(mb_substr(trim($v->pername), 0, 2));
                    @endphp

                    <div class="relative flex flex-col items-center rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-4 shadow text-center gap-2">

                        {{-- Rank badge --}}
                        @if ($rankStyle)
                            <span class="absolute -top-2.5 -right-2.5 flex h-6 w-6 items-center justify-center rounded-full text-white text-xs font-bold ring-2 ring-white dark:ring-gray-900"
                                  style="background:{{ $rankStyle['bg'] }}">
                                {{ $i + 1 }}
                            </span>
                        @else
                            <span class="absolute -top-2.5 -right-2.5 flex h-6 w-6 items-center justify-center rounded-full bg-blue-500 text-white text-xs font-bold ring-2 ring-white dark:ring-gray-900">
                                {{ $i + 1 }}
                            </span>
                        @endif

                        {{-- Avatar --}}
                        <div class="h-12 w-12 rounded-full flex items-center justify-center text-white text-sm font-bold ring-2 ring-offset-2 ring-offset-white dark:ring-offset-gray-800"
                             style="background:{{ $avatarColor }}; --tw-ring-color:{{ $avatarColor }}40">
                            {{ $initials }}
                        </div>

                        {{-- Name --}}
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 leading-tight line-clamp-2">
                            {{ $v->pername }}
                        </p>

                        {{-- Sales count + total --}}
                        <div class="text-xs text-gray-400 space-y-0.5">
                            <p>{{ number_format($v->ventas_count, 0, ',', '.') }} ventas</p>
                            <p class="font-semibold text-gray-600 dark:text-gray-300">
                                Gs. {{ number_format($v->total_ventas, 0, ',', '.') }}
                            </p>
                        </div>

                        {{-- Commission --}}
                        <div class="mt-auto w-full rounded-xl py-1.5 px-2" style="background:rgba(16,185,129,0.08)">
                            <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                Gs. {{ number_format($comision, 0, ',', '.') }}
                            </p>
                            <p class="text-[10px] text-emerald-500 dark:text-emerald-600">comisión ({{ number_format($v->porc_comision, 1, ',', '.') }}%)</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
