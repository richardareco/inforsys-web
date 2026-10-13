// Inyectar estilos para botones de sidebar en móvil
document.addEventListener('DOMContentLoaded', function() {
    // Crear un style element
    const style = document.createElement('style');
    style.textContent = `
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
    `;

    // Agregar el style element al head
    document.head.appendChild(style);
});

// También inyectar si el documento ya está cargado
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        const style = document.createElement('style');
        style.textContent = `
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
        `;
        document.head.appendChild(style);
    });
} else {
    const style = document.createElement('style');
    style.textContent = `
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
    `;
    document.head.appendChild(style);
}
