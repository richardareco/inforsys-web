<div x-data="{ tab: 'info' }" class="w-full">
    <!-- Pestañas -->
    <div class="flex space-x-1 border-b border-gray-300 mb-3">
        <button
            @click.stop="tab = 'info'"
            type="button"
            :class="tab === 'info' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'"
            class="px-3 py-2 font-medium text-xs transition-colors"
        >
            Información
        </button>
        <button
            @click.stop="tab = 'foto'"
            type="button"
            :class="tab === 'foto' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'"
            class="px-3 py-2 font-medium text-xs transition-colors"
        >
            Foto
        </button>
    </div>

    <!-- Contenido: Información -->
    <div x-show="tab === 'info'" class="space-y-2">
        <!-- Referencia y Código de Barra -->
        <div class="grid grid-cols-2 gap-2">
            <div class="bg-blue-50 dark:bg-slate-700 p-2 rounded border border-blue-200 dark:border-slate-600">
                <p class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wide">Referencia / Código</p>
                <p class="text-xs font-bold text-gray-900 dark:text-white mt-1">{{ $record->scode ?? '-' }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-slate-700 p-2 rounded border border-gray-200 dark:border-slate-600">
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide">Código de Barra</p>
                <p class="text-xs font-bold text-gray-900 dark:text-white mt-1">{{ $record->scode1 ?? '-' }}</p>
            </div>
        </div>

        <!-- Precios -->
        <div>
            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-1">Precios</p>
            <div class="grid grid-cols-3 gap-2">
                <div class="bg-blue-50 dark:bg-slate-700 p-2 rounded border border-blue-200 dark:border-blue-600 text-center">
                    <p class="text-xs text-blue-700 dark:text-blue-300 font-semibold uppercase">Precio 1</p>
                    <p class="text-sm font-bold text-blue-600 dark:text-blue-400 mt-1">Gs. {{ number_format($record->precio1, 0, ',', '.') }}</p>
                </div>
                <div class="bg-purple-50 dark:bg-slate-700 p-2 rounded border border-purple-200 dark:border-purple-600 text-center">
                    <p class="text-xs text-purple-700 dark:text-purple-300 font-semibold uppercase">Precio 2</p>
                    <p class="text-sm font-bold text-purple-600 dark:text-purple-400 mt-1">Gs. {{ number_format($record->precio2, 0, ',', '.') }}</p>
                </div>
                <div class="bg-green-50 dark:bg-slate-700 p-2 rounded border border-green-200 dark:border-green-600 text-center">
                    <p class="text-xs text-green-700 dark:text-green-300 font-semibold uppercase">Precio 3</p>
                    <p class="text-sm font-bold text-green-600 dark:text-green-400 mt-1">Gs. {{ number_format($record->precio3, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Stock por Depósito -->
        <div>
            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-1">Stock por Depósito</p>
            <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded text-sm">
                <table class="w-full text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="text-left py-1 px-2 font-semibold text-gray-700 dark:text-gray-300">Depósito</th>
                            <th class="text-left py-1 px-2 font-semibold text-gray-700 dark:text-gray-300">Código</th>
                            <th class="text-right py-1 px-2 font-semibold text-gray-700 dark:text-gray-300">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($record->depos as $depo)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="py-1 px-2 font-medium text-gray-900 dark:text-white">{{ strtoupper($depo->depo) }}</td>
                                <td class="py-1 px-2 text-gray-600 dark:text-gray-400">{{ $depo->depo }}</td>
                                <td class="py-1 px-2 text-right font-bold text-orange-600 dark:text-orange-400">{{ number_format($depo->xqty, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-1 px-2 text-center text-gray-500 dark:text-gray-400">Sin depósitos</td>
                            </tr>
                        @endforelse
                        <tr class="bg-gray-100 dark:bg-gray-800 font-semibold border-t-2 border-gray-300 dark:border-gray-600">
                            <td class="py-1 px-2 text-gray-900 dark:text-white">TOTAL</td>
                            <td class="py-1 px-2"></td>
                            <td class="py-1 px-2 text-right text-gray-900 dark:text-white">{{ number_format($record->depos->sum('xqty'), 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Contenido: Foto -->
    <div x-show="tab === 'foto'" class="w-full py-4" style="display: flex; justify-content: center; align-items: flex-start; overflow: auto;">
        @php
            $hasImage = false;
            try {
                $foto = $record->images()->first();
                $hasImage = $foto && !empty($foto->image) && strlen($foto->image) > 50;
            } catch (\Exception $e) {
                $hasImage = false;
            }
        @endphp

        @if($hasImage)
            <div class="text-center w-full px-2" style="width: 100%; display: flex; justify-content: center;">
                <img
                    src="{{ route('item-image.show', ['item' => $record->item]) }}"
                    alt="Foto de {{ $record->descr }}"
                    loading="lazy"
                    class="rounded-lg shadow-lg"
                    style="max-width: 100%; max-height: 550px; object-fit: contain; display: block;"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                />
                <div style="display: none;" class="flex flex-col items-center space-y-3 w-full">
                    <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-500 text-sm">Foto no disponible</p>
                </div>
            </div>
        @else
            <div class="text-center space-y-3 w-full">
                <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-gray-500 text-sm">Sin foto disponible</p>
            </div>
        @endif
    </div>
</div>
