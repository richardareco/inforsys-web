<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-user-group class="h-5 w-5 text-blue-400" />
                <span>Top 10 Clientes</span>
            </div>
        </x-slot>

        <div class="overflow-x-auto -mx-1">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-white/5">
                        <th class="pb-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 pl-1">#</th>
                        <th class="pb-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Cliente</th>
                        <th class="pb-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Pedidos</th>
                        <th class="pb-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Ingresos</th>
                        <th class="pb-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 pr-1">Cambio</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/[0.03]">
                    @forelse ($clientes as $i => $c)
                        @php
                            $prev    = (float) ($prevData[$c->custnr] ?? 0);
                            $curr    = (float) $c->ingresos;
                            $pct     = $prev > 0 ? (($curr - $prev) / $prev) * 100 : null;
                            $up      = $pct === null || $pct >= 0;
                        @endphp
                        <tr class="group hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-2.5 pl-1 text-xs font-bold text-gray-400">{{ $i + 1 }}</td>
                            <td class="py-2.5 max-w-[120px]">
                                <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ $c->custname }}
                                </p>
                            </td>
                            <td class="py-2.5 text-right text-sm font-semibold text-gray-600 dark:text-gray-300">
                                {{ number_format($c->pedidos, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 text-right text-sm font-bold text-gray-800 dark:text-gray-100">
                                Gs. {{ number_format($curr, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 pr-1 text-right">
                                @if ($pct !== null)
                                    <span class="inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-xs font-semibold
                                        {{ $up ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950 dark:text-rose-400' }}">
                                        @if ($up)
                                            <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                                        @else
                                            <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        @endif
                                        {{ ($pct >= 0 ? '+' : '') . number_format($pct, 1, ',', '.') }}%
                                    </span>
                                @else
                                    <span class="text-xs text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center">
                                <x-heroicon-o-inbox class="mx-auto h-8 w-8 text-gray-300" />
                                <p class="mt-2 text-sm text-gray-400">Sin datos para el período</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
