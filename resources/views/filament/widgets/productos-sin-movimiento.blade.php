<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-archive-box-x-mark class="h-5 w-5 text-slate-500" />
                <span>Productos sin movimiento ({{ $dias }} días)</span>
            </div>
        </x-slot>

        <div style="display:flex;gap:.6rem;margin-bottom:.9rem;">
            <div style="flex:1;border-radius:.8rem;padding:.6rem .8rem;background:#f1f5f9;">
                <p style="font-size:.6rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#475569;">Productos</p>
                <p style="font-size:1.35rem;font-weight:900;color:#334155;">{{ number_format($cantidad, 0, ',', '.') }}</p>
            </div>
            <div style="flex:1.6;border-radius:.8rem;padding:.6rem .8rem;background:#f5f3ff;">
                <p style="font-size:.6rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#6d28d9;">Capital inmovilizado</p>
                <p style="font-size:1.15rem;font-weight:900;color:#6d28d9;">Gs. {{ number_format($capital, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="overflow-x-auto -mx-1">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-white/5">
                        <th class="pb-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 pl-1">Producto</th>
                        <th class="pb-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Stock</th>
                        <th class="pb-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-400 pr-1">Valor al costo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/[0.03]">
                    @forelse ($items as $p)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-2.5 pl-1 max-w-[190px]"><p class="truncate text-sm font-medium text-gray-700 dark:text-gray-200">{{ $p->descr }}</p></td>
                            <td class="py-2.5 text-right text-sm text-gray-600 dark:text-gray-300">{{ number_format((float) $p->stock, 0, ',', '.') }}</td>
                            <td class="py-2.5 pr-1 text-right text-sm font-bold text-gray-800 dark:text-gray-100">Gs. {{ number_format((float) $p->capital, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-8 text-center">
                                <x-heroicon-o-check-circle class="mx-auto h-8 w-8 text-emerald-400" />
                                <p class="mt-2 text-sm text-gray-400">Todos los productos con stock tuvieron ventas</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p style="margin-top:.6rem;font-size:.68rem;color:#94a3b8;">Productos con stock que no se vendieron en los últimos {{ $dias }} días, ordenados por valor al costo.</p>
    </x-filament::section>
</x-filament-widgets::widget>
