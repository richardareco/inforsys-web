<style>
    /* Botón sidebar visible en móvil */
    .fi-topbar-open-sidebar-btn,
    .fi-topbar-close-sidebar-btn {
        background-color: #f3f4f6 !important;
        color: #1f2937 !important;
        border-radius: 0.5rem !important;
        padding: 0.5rem !important;
    }

    .fi-topbar-open-sidebar-btn:hover,
    .fi-topbar-close-sidebar-btn:hover {
        background-color: #e5e7eb !important;
        color: #111827 !important;
    }

    .fi-topbar-open-sidebar-btn svg path,
    .fi-topbar-close-sidebar-btn svg path {
        stroke: #1f2937 !important;
        fill: #1f2937 !important;
    }

    @media (max-width: 768px) {
        .fi-topbar-open-sidebar-btn,
        .fi-topbar-close-sidebar-btn {
            display: flex !important;
        }
    }

</style>

@php
    $widgets = $this->getWidgets();

    $columns = $this->getColumns();
@endphp

<x-filament-pages::page>
    @if (method_exists($this, 'filtersForm'))
        {{ $this->filtersForm }}
    @endif

    @if (count($widgets))
        <x-filament-widgets::widgets
            :data="
                [
                    'widgets' => $widgets,
                ]
            "
            :columns="$columns"
        />
    @endif
</x-filament-pages::page>
