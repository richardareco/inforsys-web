<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-clock class="h-5 w-5 text-rose-500" />
                <span>Cuentas por cobrar vencidas</span>
            </div>
        </x-slot>

        <div style="display:flex;gap:.6rem;margin-bottom:.8rem;">
            <div style="flex:1;border-radius:.8rem;padding:.6rem .8rem;background:#fef2f2;">
                <p style="font-size:.6rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#b91c1c;">Vencido</p>
                <p style="font-size:1.15rem;font-weight:900;color:#b91c1c;">Gs. {{ number_format($vencido, 0, ',', '.') }}</p>
            </div>
            <div style="flex:1;border-radius:.8rem;padding:.6rem .8rem;background:#f0f9ff;">
                <p style="font-size:.6rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#0369a1;">Por vencer</p>
                <p style="font-size:1.15rem;font-weight:900;color:#0369a1;">Gs. {{ number_format($porVencer, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Antigüedad de la deuda vencida --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.4rem;margin-bottom:.9rem;">
            @foreach($tramos as $t)
                <div style="border-radius:.6rem;padding:.4rem .5rem;border-top:3px solid {{ $t['color'] }};background:rgba(148,163,184,.08);">
                    <p style="font-size:.55rem;font-weight:700;color:#64748b;">{{ $t['label'] }}</p>
                    <p style="font-size:.72rem;font-weight:800;color:#0f172a;" class="dark:text-white">{{ number_format($t['monto'], 0, ',', '.') }}</p>
                </div>
            @endforeach
        </div>

        <div class="overflow-x-auto -mx-1">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-white/5">
                        <th class="pb-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 pl-1">Cliente</th>
                        <th class="pb-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Facturas</th>
                        <th class="pb-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Atraso</th>
                        <th class="pb-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-400 pr-1">Deuda</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/[0.03]">
                    @forelse ($clientes as $c)
                        @php $dias = (int) $c->dias; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-2.5 pl-1 max-w-[150px]"><p class="truncate text-sm font-medium text-gray-700 dark:text-gray-200">{{ $c->custname }}</p></td>
                            <td class="py-2.5 text-right text-sm text-gray-600 dark:text-gray-300">{{ $c->facturas }}</td>
                            <td class="py-2.5 text-right">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $dias > 60 ? 'bg-rose-50 text-rose-600 dark:bg-rose-950 dark:text-rose-400' : ($dias > 30 ? 'bg-orange-50 text-orange-600 dark:bg-orange-950 dark:text-orange-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-400') }}">{{ $dias }} d</span>
                            </td>
                            <td class="py-2.5 pr-1 text-right text-sm font-bold text-gray-800 dark:text-gray-100">Gs. {{ number_format((float) $c->deuda, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center">
                                <x-heroicon-o-check-circle class="mx-auto h-8 w-8 text-emerald-400" />
                                <p class="mt-2 text-sm text-gray-400">No hay deudas vencidas</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
