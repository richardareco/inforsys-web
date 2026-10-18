@php
    use Filament\Support\Enums\MaxWidth;
@endphp

<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end gap-3">
            <x-filament::button
                type="submit"
                color="primary"
            >
                Guardar cambios
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
