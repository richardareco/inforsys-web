<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-amber-500" />
                <span>Stock bajo y agotado</span>
            </div>
        </x-slot>

        <div style="display:flex;gap:.6rem;margin-bottom:.9rem;">
            <div style="flex:1;border-radius:.8rem;padding:.6rem .8rem;background:#fef2f2;">
                <p style="font-size:.6rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#b91c1c;">Agotados</p>
                <p style="font-size:1.35rem;font-weight:900;color:#b91c1c;">{{ number_format($agotados, 0, ',', '.') }}</p>
            </div>
            <div style="flex:1;border-radius:.8rem;padding:.6rem .8rem;background:#fffbeb;">
                <p style="font-size:.6rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#b45309;">Bajo el mínimo</p>
                <p style="font-size:1.35rem;font-weight:900;color:#b45309;">{{ number_format($bajos, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="overflow-x-auto -mx-1">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-white/5">
                        <th class="pb-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 pl-1">Producto</th>
                        <th class="pb-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Stock</th>
                        <th class="pb-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Vend. 30d</th>
                        <th class="pb-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-400 pr-1">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/[0.03]">
                    @forelse ($items as $p)
                        @php $agotado = (float) $p->stock <= 0; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-2.5 pl-1 max-w-[170px]">
                                <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-200">{{ $p->descr }}</p>
                            </td>
                            <td class="py-2.5 text-right text-sm font-bold text-gray-800 dark:text-gray-100">{{ number_format((float) $p->stock, 0, ',', '.') }}</td>
                            <td class="py-2.5 text-right text-sm text-gray-600 dark:text-gray-300">{{ number_format((float) $p->vendido, 0, ',', '.') }}</td>
                            <td class="py-2.5 pr-1 text-right">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $agotado ? 'bg-rose-50 text-rose-600 dark:bg-rose-950 dark:text-rose-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-400' }}">
                                    {{ $agotado ? 'Agotado' : 'Bajo' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center">
                                <x-heroicon-o-check-circle class="mx-auto h-8 w-8 text-emerald-400" />
                                <p class="mt-2 text-sm text-gray-400">Sin productos para reponer</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p style="margin-top:.6rem;font-size:.68rem;color:#94a3b8;">Incluye productos bajo su stock mínimo y los agotados con ventas en los últimos 30 días.</p>
    </x-filament::section>
</x-filament-widgets::widget>
