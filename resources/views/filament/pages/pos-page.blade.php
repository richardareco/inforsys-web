<x-filament-panels::page>
<style>
/* ─── Ocultar texto de botón Pantalla completa ─── */
[wire\:target="mountAction('fullscreen')"] ~ span,
button[wire\:click*="fullscreen"] .fi-btn-label,
.fi-ac-btn-action:has(svg[wire\:target*="fullscreen"]) .fi-btn-label,
.fi-ac-btn-action:has(svg[wire\:target*="fullscreen"]) span {
    display: none !important;
}
.fi-ac-btn-action:has(svg[wire\:target*="fullscreen"]) {
    width: 2.4rem !important;
    min-width: 2.4rem !important;
    position: absolute !important;
    top: 0 !important;
    right: 0 !important;
}

@media (max-width: 768px) {
    .fi-ac-btn-action:has(svg[wire\:target*="fullscreen"]) {
        position: absolute !important;
        top: -2.8rem !important;
        right: 0 !important;
    }

    /* Mostrar botones expand/collapse sidebar en móvil */
    .fi-topbar-close-sidebar-btn,
    .fi-topbar-open-sidebar-btn {
        display: flex !important;
    }
}

/* ─── Contenedor principal ─── */
.pos-wrap {
    display: flex;
    flex-direction: row;
    height: calc(100vh - 160px); min-height: 500px;
    border: 1px solid #e5e7eb; border-radius: 1rem;
    overflow: hidden;
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
}

/* Layout responsive para móviles */
@media (max-width: 768px) {
    .pos-wrap {
        flex-direction: column;
        height: 100vh;
        height: 100dvh;
        border-radius: 0;
        border: none;
        padding-top: env(safe-area-inset-top);
        padding-bottom: env(safe-area-inset-bottom);
        padding-left: env(safe-area-inset-left);
        padding-right: env(safe-area-inset-right);
        overflow: hidden;
    }
}

/* Adaptación cuando la sidebar está abierta en desktop */
@media (min-width: 1024px) {
    .pos-wrap {
        width: 100%;
        transition: width 0.3s ease;
    }
}

/* Cuando hay sidebar visible en desktop */
@media (min-width: 1024px) and (prefers-reduced-motion: no-preference) {
    .fi-sidebar:not(.hidden) ~ .fi-page .pos-wrap {
        width: calc(100% - 300px);
        margin-left: auto;
    }
}

/* Mostrar botón de sidebar toggle */
[data-action="sidebar-toggle"],
[aria-label*="Toggle sidebar"],
button[aria-label*="toggle sidebar"],
.fi-topbar button[aria-expanded] {
    display: flex !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Estilos del botón sidebar - Fuera del media query para mayor especificidad */
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

/* Remover línea blanca entre sidebar y página */
.fi-sidebar {
    border: none !important;
    border-right: none !important;
    box-shadow: none !important;
    outline: none !important;
}

.fi-sidebar::after {
    display: none !important;
}

.fi-page {
    border: none !important;
    box-shadow: none !important;
    outline: none !important;
    border-left: none !important;
}

.fi-main-ctn {
    border: none !important;
    box-shadow: none !important;
    border-left: none !important;
}

/* Ocultar todo tipo de separadores entre sidebar y página */
.fi-sidebar ~ * {
    border-left: none !important;
}

/* Fondo gris de la página más oscuro para camuflar la línea */
.fi-page {
    background: #f3f4f6 !important;
}

/* Cuando sidebar está colapsada (hidden), expandir el contenido */
.fi-sidebar.hidden ~ .fi-page {
    width: 100% !important;
    margin-left: 0 !important;
    padding-left: 0 !important;
}

.fi-sidebar.hidden ~ .fi-page .pos-wrap {
    width: 100% !important;
    max-width: 100% !important;
}

/* Remover línea divisoria cuando sidebar está visible */
.fi-sidebar:not(.hidden) {
    margin-right: -1px;
}

.fi-sidebar:not(.hidden) ~ .fi-page {
    margin-left: 0 !important;
}

/* Ocultar línea blanca del encabezado del carrito cuando sidebar está abierto */
.fi-sidebar:not(.hidden) ~ .fi-page .cart-header {
    border-bottom: none !important;
    border-top: none !important;
}

.fi-sidebar:not(.hidden) ~ .fi-page .pos-left-top {
    border-bottom: none !important;
}

/* Ocultar línea en el área de filtros cuando sidebar está visible */
.fi-sidebar:not(.hidden) ~ .fi-page [style*="border-top"] {
    border-top: none !important;
}

/* Ocultar completamente el filtro de búsqueda cuando el sidebar está abierto */
.fi-sidebar:not(.hidden) ~ .fi-page .pos-search-wrap {
    pointer-events: none !important;
    visibility: hidden !important;
}

.fi-sidebar:not(.hidden) ~ .fi-page .pos-left-top {
    visibility: hidden !important;
    pointer-events: none !important;
}

@media (max-width: 768px) {
    .fi-sidebar:not(.hidden) ~ .fi-page .cart-header {
        border-bottom: none !important;
    }

    .fi-sidebar:not(.hidden) ~ .fi-page .pos-wrap {
        z-index: 0 !important;
    }

    .fi-sidebar:not(.hidden) ~ .fi-page .pos-left {
        z-index: 0 !important;
    }
}

/* Ocultar scrollbar pero permitir scroll */
::-webkit-scrollbar {
    width: 0px;
    height: 0px;
    background: transparent;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: transparent;
}

html {
    scrollbar-width: none;
    scrollbar-color: transparent transparent;
}
.dark .pos-wrap { border-color: #374151; }

/* ─── Panel izquierdo: depósito + búsqueda + productos ─── */
.pos-left {
    flex: 1; display: flex; flex-direction: column; overflow: hidden;
    background: #f9fafb;
    order: 1;
}
.dark .pos-left { background: #111827; }

@media (max-width: 768px) {
    .pos-left {
        flex: 1;
        order: 1;
        overflow-y: auto;
    }
}

.pos-left-top {
    flex-shrink: 0; padding: .35rem .6rem !important;
    background: white; border-bottom: none;
    display: flex; flex-direction: column; gap: .3rem !important;
}
.dark .pos-left-top { background: #1f2937; border-color: #374151; }

@media (max-width: 768px) {
    .pos-left-top {
        padding: .3rem .4rem !important;
        gap: .2rem !important;
    }
}

/* ─── Buscador ─── */
.pos-search-wrap {
    position: relative;
    display: flex;
    align-items: center;
    background: white;
    border: 2px solid #6366f1;
    border-radius: .6rem;
    overflow: hidden;
    box-shadow: 0 0 0 3px rgba(99,102,241,.1);
}
.dark .pos-search-wrap { background: #1f2937; border-color: #4f46e5; }

.pos-search-icon {
    flex-shrink: 0;
    padding: .4rem .6rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    pointer-events: none;
}
.dark .pos-search-icon { color: #d1d5db; }

.pos-search {
    flex: 1;
    padding: .4rem .3rem !important;
    border: none !important;
    font-size: .8rem !important;
    font-weight: 500;
    background: transparent;
    color: #111827;
    outline: none;
    box-shadow: none !important;
}
.dark .pos-search { background: transparent; color: #f9fafb; }

.pos-search::placeholder { color: #9ca3af; }
.dark .pos-search::placeholder { color: #d1d5db; }

@media (max-width: 768px) {
    .pos-search {
        padding: .32rem .3rem !important;
        font-size: .75rem !important;
    }
    .pos-search-wrap {
        border-width: 1.5px !important;
    }
}

/* ─── Dropdown resultados (móvil) ─── */
.mobile-dropdown {
    display: none;
    background: white;
    overflow-y: auto;
    max-height: 60vh;
}
.dark .mobile-dropdown { background: #111827; }
.mobile-dropdown.show {
    display: block;
}

/* Grid móvil de productos */
.mobile-products-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.5rem;
    padding: 0.5rem;
}

.mob-item {
    display: flex; flex-direction: row; gap: 0.5rem;
    padding: 0.5rem; border-radius: 0.5rem;
    background: white; border: 1px solid #e5e7eb;
    cursor: pointer; transition: all 0.15s; user-select: none;
    align-items: center;
}
.dark .mob-item { background: #1f2937; border-color: #374151; }
.mob-item:hover { border-color: #6366f1; box-shadow: 0 2px 8px rgba(99,102,241,.15); transform: translateY(-1px); }
.mob-item:active { transform: scale(0.97); }

.mob-item-image {
    width: 80px; height: 80px; object-fit: contain;
    background: #f3f4f6; border-radius: 0.4rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.dark .mob-item-image { background: #374151; }

.mob-item-info {
    display: flex; flex-direction: column; gap: 0.25rem;
    flex: 1; min-width: 0;
}

.mob-item-name {
    font-size: 0.75rem; font-weight: 700;
    color: #111827; line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.dark .mob-item-name { color: #f9fafb; }

.mob-item-specs {
    font-size: 0.6rem; color: #9ca3af; font-weight: 500;
    display: none;
}
.dark .mob-item-specs { color: #d1d5db; }

.mob-item-price {
    font-size: 0.8rem; font-weight: 900;
    color: #4f46e5;
}

.mob-item-footer {
    display: flex; gap: 0.4rem; align-items: center;
}

.mob-item-stock {
    font-size: 0.55rem; font-weight: 700;
    padding: 0.08rem 0.25rem; border-radius: 999px;
    display: inline-block;
    width: fit-content;
    flex-shrink: 0;
}

.mob-item-stock.high { background: #dcfce7; color: #16a34a; }
.mob-item-stock.medium { background: #fef9c3; color: #92400e; }
.mob-item-stock.low { background: #fee2e2; color: #b91c1c; }

.mob-item-add-btn {
    width: 2.2rem; height: 2.2rem; padding: 0;
    background: #6366f1; color: white;
    border: none; border-radius: 0.4rem;
    font-size: 0.75rem; font-weight: 700;
    cursor: pointer; display: flex;
    align-items: center; justify-content: center;
    gap: 0; transition: background 0.12s;
    flex-shrink: 0;
}
.mob-item-add-btn:hover { background: #4f46e5; }
.dark .mob-item-add-btn { background: #6366f1; }
.mob-item-add-btn svg { width: 0.8rem; height: 0.8rem; }

/* ─── Grid de productos ─── */
.pos-products {
    flex: 1; overflow-y: auto; padding: .4rem .75rem .75rem .75rem;
}
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: .4rem;
}

@media (max-width: 768px) {
    .pos-products {
        padding: .3rem .5rem .5rem .5rem;
    }
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: .3rem;
    }
}
.product-card {
    background: white; border-radius: .75rem; border: 1.5px solid #e5e7eb;
    padding: .5rem; display: flex; flex-direction: column; gap: .25rem;
    cursor: pointer; transition: all .15s; user-select: none;
    aspect-ratio: 1;
}
.dark .product-card { background: #1f2937; border-color: #374151; }
.product-card:hover { border-color: #6366f1; box-shadow: 0 2px 12px rgba(99,102,241,.15); transform: translateY(-1px); }
.product-card:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(99,102,241,.25); }
.product-card:active { transform: scale(.97); }

.product-image {
    width: 100%; height: 70px; object-fit: contain;
    background: #f3f4f6; border-radius: .4rem;
    margin: -0.5rem -0.5rem 0.2rem -0.5rem; padding: 0.3rem;
    display: flex; align-items: center; justify-content: center;
}
.dark .product-image { background: #374151; }
.product-image.no-image {
    display: flex; align-items: center; justify-content: center;
    color: #9ca3af; font-size: 0.65rem;
}

.product-add-btn {
    margin-top: auto; width: 100%; padding: .4rem;
    background: #6366f1; color: white; border: none; border-radius: .55rem;
    font-size: .78rem; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: .25rem;
    transition: background .12s;
}
.product-add-btn:hover { background: #4f46e5; }

/* ─── Panel derecho: carrito ─── */
.pos-cart {
    width: 350px; flex-shrink: 0;
    display: flex; flex-direction: column;
    border-left: 1px solid #e5e7eb; background: white;
    order: 2;
}

@media (min-width: 1200px) {
    .pos-cart {
        width: 400px;
    }
}
.dark .pos-cart { background: #1f2937; border-color: #374151; }

@media (max-width: 768px) {
    .pos-cart {
        width: 100%;
        flex: 0.4;
        border-left: none;
        border-top: 1px solid #e5e7eb;
        order: 2;
        overflow: hidden;
        display: flex;
    }
}

/* Meta: cliente + cajero */
.cart-meta {
    flex-shrink: 0; padding: .65rem .85rem;
    border-bottom: 1px solid #e5e7eb; background: #fafafa;
    display: flex; flex-direction: column; gap: .45rem;
}
.dark .cart-meta { background: #111827; border-color: #374151; }

@media (max-width: 768px) {
    .cart-meta {
        padding: .2rem .35rem !important;
        gap: .15rem !important;
        background: transparent;
        border: none !important;
    }
}

.sel-label { font-size: .58rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: .15rem; }

@media (max-width: 768px) {
    .sel-label {
        display: none !important;
    }
    .pos-selects-row {
        padding: 0 !important;
        margin: 0 !important;
        display: flex !important;
        align-items: center !important;
        gap: 0.3rem !important;
    }
    .pos-selects-row > div {
        min-width: auto !important;
        flex: 1 !important;
    }
    .pos-sel {
        appearance: none !important;
        border: 1px solid #d1d5db !important;
        background: #f9fafb !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%236b7280'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right .3rem center !important;
        background-size: 0.8rem !important;
        padding: .25rem .3rem !important;
        font-size: 0.7rem !important;
        font-weight: 600 !important;
        color: #111827 !important;
        cursor: pointer !important;
        border-radius: .35rem !important;
    }
}

/* Fix selects */
.pos-sel {
    -webkit-appearance: none; -moz-appearance: none; appearance: none;
    background-color: #f9fafb;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%236b7280'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right .55rem center; background-size: 1rem;
    padding: .38rem 1.9rem .38rem .65rem;
    border: 1px solid #d1d5db; border-radius: .55rem;
    font-size: .8rem; color: #111827; cursor: pointer; width: 100%;
}
.dark .pos-sel { background-color: #374151; border-color: #4b5563; color: #f9fafb; }
.pos-sel:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,.2); }

/* ─── Combobox de cliente ─── */
.cliente-combo-input {
    width: 100%; padding: .38rem 1.8rem .38rem .65rem;
    border: 1px solid #d1d5db; border-radius: .55rem;
    font-size: .8rem; background: #f9fafb; color: #111827; outline: none; transition: border-color .12s;
}
.dark .cliente-combo-input { background: #374151; border-color: #4b5563; color: #f9fafb; }
.cliente-combo-input:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,.2); }

@media (max-width: 768px) {
    .cliente-combo-input {
        padding: .25rem .4rem !important;
        font-size: .7rem !important;
        border-width: 1px !important;
    }
}

/* ─── Encabezado del carrito con total visible ─── */
.cart-header {
    flex-shrink: 0; border-bottom: 2px solid #e5e7eb;
    background: #ffffff;
}
.dark .cart-header { background: #ffffff; border-color: #e5e7eb; }
.cart-header-top {
    display: flex; align-items: center; gap: .4rem;
    padding: .4rem .75rem;
    font-size: .8rem; font-weight: 700; color: #94a3b8;
}

@media (max-width: 768px) {
    .cart-header {
        padding: .15rem .35rem !important;
    }
    .cart-header-top {
        padding: .1rem .3rem;
        font-size: .6rem;
    }
}
.cart-header-total {
    padding: .25rem .75rem .6rem;
    text-align: center;
}
.cart-header-total .total-label {
    font-size: .65rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .12em; color: #0047AB; margin-bottom: .05rem;
}
.cart-header-total .total-amount {
    font-size: 2.6rem; font-weight: 900; color: #0047AB;
    line-height: 1; letter-spacing: -.03em;
}

@media (max-width: 768px) {
    .cart-header-total {
        padding: .15rem .35rem !important;
    }
    .cart-header-total .total-label {
        font-size: .5rem !important;
        margin-bottom: 0 !important;
    }
    .cart-header-total .total-amount {
        font-size: 1.3rem !important;
        line-height: 1 !important;
    }
}

/* ─── Cuerpo del carrito ─── */
.cart-body {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}

/* ─── Resumen del carrito (siempre visible abajo) ─── */
.cart-summary {
    flex-shrink: 0;
    padding: .5rem .75rem;
    border-top: 1px solid #e5e7eb;
    background: #fafafa;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: .75rem;
    font-weight: 600;
    color: #6b7280;
}
.dark .cart-summary { background: #111827; border-color: #374151; }
.cart-summary-count { color: #111827; }
.dark .cart-summary-count { color: #f9fafb; }
.cart-summary-total { font-size: 1rem; font-weight: 900; color: #0047AB; }

@media (max-width: 768px) {
    .cart-summary { display: none; }
}
.cart-row {
    display: flex; align-items: center; gap: .6rem; padding: .6rem .75rem;
    border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: background .1s;
    font-size: .8rem;
}
.dark .cart-row { border-color: #374151; }
.cart-row:hover { background: #fafafa; }
.cart-row:focus { outline: none; background: #f5f5f5; }
.dark .cart-row:hover { background: rgba(99,102,241,.08); }
.dark .cart-row:focus { background: rgba(99,102,241,.15); }

/* Imagen del producto en el carrito */
.cart-item-image {
    width: 3.5rem; height: 3.5rem;
    flex-shrink: 0; border-radius: .5rem;
    object-fit: cover; background: #f3f4f6;
}
.del-btn {
    flex-shrink: 0; width: 1.6rem; height: 1.6rem; border-radius: .4rem;
    border: 1px solid #fecaca; background: #fff5f5; color: #ef4444;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; padding: 0; transition: all .12s;
}
.del-btn:hover { background: #fee2e2; }

@media (max-width: 768px) {
    .cart-row {
        padding: .25rem .35rem !important;
        gap: .3rem !important;
        font-size: .7rem !important;
    }
    .cart-row p {
        font-size: .7rem !important;
        line-height: 1.2 !important;
    }
    .del-btn {
        width: 1.3rem !important;
        height: 1.3rem !important;
    }
    #cart-footer-full {
        display: none !important;
    }
    #cart-footer-compact {
        display: flex !important;
    }
    .dark #cart-footer-compact {
        background: #1f2937 !important;
        border-color: #374151 !important;
    }
    #cart-footer-compact button svg {
        width: .7rem !important;
        height: .7rem !important;
    }
}

/* Desktop: mostrar footer completo, ocultar compacto */
@media (min-width: 769px) {
    #cart-footer-full {
        display: flex !important;
    }
    #cart-footer-compact {
        display: none !important;
    }
}

/* ─── Footer del carrito: total + cobrar ─── */
.cart-footer {
    flex-shrink: 0; padding: .75rem 1rem;
    border-top: 2px solid #e5e7eb; background: white;
    display: flex; align-items: center; gap: .75rem;
    flex-direction: row;
}
.dark .cart-footer { background: #1f2937; border-color: #374151; }
.cobrar-btn {
    display: flex; align-items: center; gap: .3rem; padding: .5rem .7rem;
    background: #4f46e5; color: white; font-weight: 900; font-size: .75rem;
    border: none; border-radius: .75rem; cursor: pointer;
    box-shadow: 0 3px 12px rgba(79,70,229,.35); transition: all .15s; white-space: nowrap;
}
.cobrar-btn:hover { background: #4338ca; transform: translateY(-1px); }
.cobrar-btn:active { transform: scale(.97); }
.cobrar-btn:disabled { background: #e5e7eb; color: #9ca3af; cursor: not-allowed; box-shadow: none; transform: none; }

@media (max-width: 768px) {
    .cart-footer {
        padding: .3rem .35rem !important;
        gap: .25rem !important;
        flex-direction: row !important;
    }
    .cart-footer button {
        flex: 1 !important;
        padding: .35rem !important;
        font-size: .65rem !important;
        gap: 0 !important;
    }
    .cart-footer button svg {
        width: .65rem !important;
        height: .65rem !important;
    }
    .cart-footer button span {
        display: none !important;
    }
    .cobrar-btn svg {
        width: .7rem !important;
        height: .7rem !important;
    }
}

/* ─── Modales ─── */
.modal-back {
    position: fixed; inset: 0; z-index: 50;
    background: rgba(0,0,0,.55); backdrop-filter: blur(4px);
    display: flex; align-items: flex-end; justify-content: center;
}
@media(min-width:640px){ .modal-back { align-items: center; } }
.modal-box {
    background: white; width: 75% !important; max-width: 280px !important;
    border-radius: 1.25rem 1.25rem 0 0; padding: 0.8rem 0.6rem !important;
    box-shadow: 0 -8px 30px rgba(0,0,0,.2);
    font-size: 0.8rem !important;
    max-height: 85vh;
    overflow-y: auto;
}
@media(min-width:640px){
    .modal-box {
        border-radius: 1.25rem;
        width: 100%;
        max-width: 400px;
        padding: 1.4rem;
        font-size: 1rem;
    }
}
.dark .modal-box { background: #1f2937; }
.pay-method {
    flex: 1; display: flex; flex-direction: column; align-items: center; gap: .4rem;
    padding: .85rem .4rem; border-radius: .85rem; border: 2px solid #e5e7eb;
    font-size: .78rem; font-weight: 700; cursor: pointer; transition: all .15s;
    background: white; color: #6b7280;
}
.dark .pay-method { background: #1f2937; border-color: #374151; }
.pay-method.sel-green  { border-color: #22c55e; background: #f0fdf4; color: #16a34a; }
.pay-method.sel-blue   { border-color: #3b82f6; background: #eff6ff; color: #1d4ed8; }
.pay-method.sel-purple { border-color: #a855f7; background: #faf5ff; color: #7e22ce; }

.qty-ctrl { display: flex; align-items: center; gap: .2rem; margin-bottom: .4rem; }
.qty-btn {
    width: 1.5rem !important; height: 1.5rem !important; border-radius: .25rem;
    border: 0.5px solid #d1d5db; background: #f9fafb; color: #374151;
    font-size: 0.7rem !important; font-weight: 700; cursor: pointer; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; transition: all .12s;
    padding: 0 !important;
}
.qty-btn:hover { background: #ede9fe; border-color: #6366f1; color: #6366f1; }
.qty-num {
    flex: 1; text-align: center; padding: .25rem !important;
    border: 0.5px solid #d1d5db; border-radius: .25rem;
    font-size: 0.75rem !important; font-weight: 800; color: #111827; outline: none; background: white;
}
.qty-num:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,.12); }
.dark .qty-num { background: #374151; color: #f9fafb; border-color: #4b5563; }

/* ─── Fullscreen ─── */
/* En móvil: el menú lateral sigue disponible (se abre con el botón de hamburguesa) */
@media (max-width: 1024px) {
    body.pos-fullscreen .fi-main-ctn { margin-left: 0 !important; padding-left: 0 !important; }
}
/* En desktop: mantener sidebar visible en fullscreen */
@media (min-width: 1025px) {
    body.pos-fullscreen .fi-main-ctn { padding-left: 0 !important; }
}

/* ─── Fila de selects (cliente, cajero, depósito) ─── */
.pos-selects-row { display: flex; gap: .6rem; flex-wrap: wrap; }

@media (max-width: 768px) {
    .pos-selects-row {
        gap: .3rem !important;
        align-items: center;
    }
    .pos-selects-row > div {
        min-width: auto;
    }
    .pos-selects-row label {
        display: none !important;
    }
    .pos-selects-row select {
        padding: .25rem .4rem !important;
        font-size: .7rem !important;
        border-width: 1px !important;
    }
}

/* ─── Responsive: móvil ─── */
@media (max-width: 767px) {
    .pos-wrap { flex-direction: column; height: auto; border-radius: .85rem; overflow: visible; }
    .pos-left { background: white; overflow: visible; }
    .pos-left-top { border-radius: .85rem .85rem 0 0; overflow: visible; }
    .pos-products { display: none; }
    .mobile-dropdown.show { display: block; z-index: 20; }
    .pos-cart { width: 100%; border-left: none; border-top: 1px solid #e5e7eb; }
    .dark .pos-cart { border-color: #374151; }
    .cart-footer { position: sticky; bottom: 0; z-index: 10; flex-direction: column !important; gap: 0.5rem !important; padding: 0.5rem 0.5rem !important; }
    .cart-footer button { width: 100% !important; flex-direction: column !important; justify-content: center !important; padding: 0.6rem 0.8rem !important; font-size: 0.8rem !important; }
    .cobrar-btn { padding: 0.6rem 0.8rem !important; }
    .cobrar-btn svg { width: 1.2rem !important; height: 1.2rem !important; }
    .cart-footer button svg { width: 1rem !important; height: 1rem !important; }
    .cart-footer button span { font-size: 0.65rem !important; }

}

@media (min-width: 768px) {
    .mobile-dropdown { display: none !important; }
}

@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Adaptación para teclado visible (viewport comprimido) ─── */
@media (max-height: 600px) and (max-width: 768px) {
    .pos-left {
        overflow-y: auto;
        max-height: 40vh;
    }
    .pos-cart {
        max-height: 40vh;
        overflow-y: auto;
    }
    .cart-body {
        max-height: 15vh;
    }
    .pos-search {
        font-size: .65rem !important;
    }
}

@media (max-height: 500px) and (max-width: 768px) {
    .pos-left-top {
        padding: .15rem .25rem !important;
        gap: .1rem !important;
    }
    .mobile-dropdown {
        max-height: 30vh !important;
    }
    .cart-header {
        padding: .1rem .25rem !important;
    }
    .cart-body {
        min-height: 10vh;
    }
}


/* ─── Clase activada cuando teclado está visible ─── */
body.pos-keyboard-visible .pos-left-top {
    min-height: auto;
}

body.pos-keyboard-visible .pos-left-top > div:first-child,
body.pos-keyboard-visible .sel-label {
    display: none !important;
}

body.pos-keyboard-visible .mobile-dropdown {
    max-height: 40vh !important;
}

body.pos-keyboard-visible .pos-wrap {
    flex-wrap: wrap;
}

/* Mostrar resumen compacto cuando hay teclado */
body.pos-keyboard-visible .pos-left::after {
    content: '';
    display: flex;
    flex-shrink: 0;
    height: auto;
    padding: .2rem .35rem;
    background: #f3f4f6;
    border-top: 1px solid #e5e7eb;
    font-size: .65rem;
    font-weight: 700;
    color: #6b7280;
    gap: .3rem;
    order: 100;
}

/* ══════════════ PULIDO VISUAL (solo apariencia) ══════════════ */
.pos-wrap {
    border: 1px solid rgba(148, 163, 184, .28);
    border-radius: 1.25rem;
    background: #fff;
    box-shadow: 0 18px 40px -22px rgba(15, 23, 42, .35), 0 2px 6px rgba(15, 23, 42, .04);
}
@media (max-width: 768px) {
    .pos-wrap { border-radius: 0; box-shadow: none; }
}

/* Panel izquierdo */
.pos-left { background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); }
.pos-left-top { background: rgba(255,255,255,.92); backdrop-filter: blur(6px); }

/* Buscador */
.pos-search-wrap {
    border: 1.5px solid #c7d2fe;
    border-radius: .9rem;
    background: #fff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
    transition: border-color .18s ease, box-shadow .18s ease;
}
.pos-search-wrap:focus-within {
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, .14), 0 6px 18px -8px rgba(79, 70, 229, .45);
}
.pos-search-icon { color: #818cf8; }

/* Tarjetas de producto */
.product-card {
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}
.product-card:hover {
    border-color: #a5b4fc;
    box-shadow: 0 12px 24px -12px rgba(79, 70, 229, .35);
    transform: translateY(-3px);
}
.product-image { border-radius: .75rem; background: #ffffff; }
.mob-item-image, .cart-item-image { background: #ffffff; }
.product-add-btn {
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    border-radius: .7rem;
    box-shadow: 0 4px 10px -4px rgba(99, 102, 241, .6);
    transition: filter .15s ease, box-shadow .15s ease, transform .15s ease;
}
.product-add-btn:hover { background: linear-gradient(135deg, #4f46e5, #6d28d9); filter: brightness(1.05); }

/* Resultados en móvil */
.mob-item { border-radius: .9rem; border-color: #e2e8f0; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }
.mob-item-add-btn { background: linear-gradient(135deg, #6366f1, #7c3aed); }

/* Carrito */
.pos-cart { border-left: 1px solid #e2e8f0; box-shadow: -10px 0 28px -22px rgba(15, 23, 42, .35); }
.cart-header { background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); }
.cliente-combo-input, .pos-sel { border-radius: .6rem; transition: border-color .15s ease, box-shadow .15s ease; }
.cart-header-total .total-amount {
    background: linear-gradient(135deg, #0047AB, #0a6cdc);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
.cart-row { border-bottom: 1px solid #eef2f7; transition: background .15s ease; }
.cart-row:hover { background: #f5f7ff; }
.cart-item-image { border-radius: .75rem; box-shadow: 0 1px 3px rgba(15, 23, 42, .12); }
.del-btn { border-radius: .55rem; transition: background .15s ease, transform .15s ease; }
.del-btn:hover { background: #fee2e2; transform: scale(1.08); }
.cart-summary { background: #f8fafc; border-top: 1px solid #e2e8f0; }
.cart-footer { border-top: 1px solid #e2e8f0; background: linear-gradient(180deg, #ffffff, #f8fafc); }

/* Botón cobrar */
.cobrar-btn {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    border-radius: .9rem;
    box-shadow: 0 8px 18px -6px rgba(79, 70, 229, .55), inset 0 1px 0 rgba(255, 255, 255, .22);
    transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
}
.cobrar-btn:hover {
    background: linear-gradient(135deg, #4338ca, #6d28d9);
    box-shadow: 0 12px 24px -8px rgba(79, 70, 229, .65), inset 0 1px 0 rgba(255, 255, 255, .25);
}
.cobrar-btn:disabled { background: #e5e7eb; box-shadow: none; }

/* Modales */
.modal-back { backdrop-filter: blur(6px); background: rgba(15, 23, 42, .55); }
.modal-box {
    border-radius: 1.4rem;
    box-shadow: 0 30px 70px -20px rgba(15, 23, 42, .55), 0 0 0 1px rgba(148, 163, 184, .18);
}
.pay-method { border-radius: .9rem; transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease; }
.pay-method:hover { transform: translateY(-2px); box-shadow: 0 8px 16px -10px rgba(15, 23, 42, .4); }
.qty-btn { border-radius: .6rem; }

/* Modal de cobro: formas de pago con ficha de ícono */
.pay-method { gap: .6rem; padding: 1rem .5rem .85rem; border: 2px solid #e2e8f0; border-radius: 1rem; }
.pay-method .pm-ico {
    width: 3rem; height: 3rem; border-radius: 1rem;
    display: grid; place-items: center;
    background: #f1f5f9; color: #64748b;
    transition: background .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease;
}
.pay-method .pm-ico svg { width: 1.65rem; height: 1.65rem; }
.pay-method:hover .pm-ico { transform: scale(1.06); }
.pay-method.sel-green  { box-shadow: 0 10px 22px -12px rgba(34, 197, 94, .7); }
.pay-method.sel-blue   { box-shadow: 0 10px 22px -12px rgba(59, 130, 246, .7); }
.pay-method.sel-purple { box-shadow: 0 10px 22px -12px rgba(168, 85, 247, .7); }
.pay-method.sel-green  .pm-ico { background: linear-gradient(135deg, #22c55e, #15803d); color: #fff; box-shadow: 0 6px 14px -4px rgba(22, 163, 74, .65); }
.pay-method.sel-blue   .pm-ico { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; box-shadow: 0 6px 14px -4px rgba(37, 99, 235, .65); }
.pay-method.sel-purple .pm-ico { background: linear-gradient(135deg, #a855f7, #7e22ce); color: #fff; box-shadow: 0 6px 14px -4px rgba(147, 51, 234, .65); }

/* Total, monto y botones del modal de cobro */
.pm-total {
    background: linear-gradient(135deg, #eef2ff, #f5f3ff) !important;
    border: 1px solid #e0e7ff;
    border-radius: 1rem !important;
}
.pm-monto { border: 1px solid #bbf7d0; border-radius: 1rem !important; }
.pm-monto input:focus { border-color: #22c55e !important; box-shadow: 0 0 0 4px rgba(34, 197, 94, .15); }
.pm-cancel { border-radius: .9rem !important; transition: background .15s ease, border-color .15s ease; }
.pm-cancel:hover { background: #f8fafc !important; border-color: #cbd5e1 !important; }
.pm-confirm {
    background: linear-gradient(135deg, #4f46e5, #7c3aed) !important;
    border-radius: .9rem !important;
    box-shadow: 0 10px 22px -8px rgba(79, 70, 229, .6), inset 0 1px 0 rgba(255, 255, 255, .22) !important;
    transition: transform .18s ease, filter .18s ease !important;
}
.pm-confirm:hover { filter: brightness(1.08); transform: translateY(-1px); }
.pm-confirm:disabled { opacity: .7; cursor: wait; transform: none; }


/* Categorías: chips más grandes y modernos */
.pos-cats { display: flex; flex-wrap: wrap; gap: .5rem; padding: .35rem 0 .25rem; }
.pos-cat {
    padding: .45rem 1.05rem;
    border-radius: 9999px;
    border: 1.5px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    font-size: .8rem;
    font-weight: 700;
    letter-spacing: .01em;
    white-space: nowrap;
    flex-shrink: 0;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
    transition: transform .15s ease, box-shadow .15s ease, background .15s ease, border-color .15s ease, color .15s ease;
}
.pos-cat:hover { background: #eef2ff; border-color: #a5b4fc; color: #4338ca; transform: translateY(-1px); }
.pos-cat.on {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    border-color: transparent;
    color: #ffffff;
    box-shadow: 0 8px 16px -8px rgba(79, 70, 229, .75);
}
.pos-cat.on:hover { color: #ffffff; filter: brightness(1.06); }
@media (max-width: 768px) {
    .pos-cat { padding: .38rem .85rem; font-size: .74rem; }
}

/* Botón y modal del lector de código de barras */
.pos-scan-btn {
    flex-shrink: 0;
    width: 2.1rem; height: 2.1rem; margin: .2rem .25rem .2rem 0;
    border: none; border-radius: .65rem; cursor: pointer;
    display: grid; place-items: center;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    color: #fff;
    box-shadow: 0 4px 10px -4px rgba(99, 102, 241, .65), inset 0 1px 0 rgba(255,255,255,.22);
    transition: transform .15s ease, filter .15s ease, box-shadow .15s ease;
}
.pos-scan-btn svg { width: 1.2rem; height: 1.2rem; }
.pos-scan-btn:hover { filter: brightness(1.08); transform: translateY(-1px); box-shadow: 0 8px 16px -6px rgba(99, 102, 241, .7); }
.pos-scan-btn:active { transform: scale(.94); }

.scan-box { text-align: center; }
.scan-head { display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem; text-align: left; margin-bottom: .8rem; }
.scan-title { font-size: 1.05rem; font-weight: 900; color: #111827; }
.scan-sub { font-size: .75rem; color: #6b7280; margin-top: .1rem; }
.scan-close {
    flex-shrink: 0; width: 2rem; height: 2rem; border-radius: .6rem; border: 1px solid #e5e7eb;
    background: #fff; color: #6b7280; display: grid; place-items: center; cursor: pointer; transition: background .15s ease;
}
.scan-close:hover { background: #f3f4f6; }
.scan-close svg { width: 1rem; height: 1rem; }
.scan-view {
    border-radius: 1rem; overflow: hidden; background: #0f172a;
    min-height: 220px; box-shadow: inset 0 0 0 2px rgba(99, 102, 241, .35);
}
#barcode-reader { width: 100%; }
#barcode-reader video { width: 100% !important; display: block; }
.scan-status { margin-top: .7rem; font-size: .8rem; color: #6b7280; }
.scan-error { margin-top: .7rem; font-size: .8rem; font-weight: 600; color: #b91c1c; background: #fef2f2; border-radius: .7rem; padding: .6rem .8rem; }
.scan-cancel {
    margin-top: .9rem; width: 100%; padding: .7rem; border-radius: .9rem; border: 1.5px solid #e5e7eb;
    background: transparent; font-weight: 700; color: #6b7280; cursor: pointer; transition: background .15s ease;
}
.scan-cancel:hover { background: #f8fafc; }

/* Ancho real de los modales de cobro e impresión (la regla base los limitaba a 400px) */
@media (min-width: 640px) {
    .modal-box[style*="max-width:550px"] { max-width: 550px !important; width: 100% !important; padding: 1.4rem !important; }
    .modal-box[style*="max-width:420px"] { max-width: 420px !important; width: 100% !important; padding: 1.4rem !important; }
    .modal-box[style*="max-width:480px"] { max-width: 480px !important; width: 100% !important; padding: 1.4rem !important; }
}
</style>

<div x-data="posApp()" data-cart-key="pos-cart:{{ auth()->id() }}:{{ auth()->user()?->empresa_id ?? 0 }}" @focus-search.window="$nextTick(() => $refs.search?.focus())" @toggle-fullscreen.window="toggleFullscreen()">

<div class="pos-wrap">

    {{-- ══════ PANEL IZQUIERDO: depósito + búsqueda + productos ══════ --}}
    <div class="pos-left">

        {{-- Header izquierdo --}}
        <div class="pos-left-top">

            {{-- Fila: Cajero --}}
            <div class="pos-selects-row" style="align-items: center; gap: 0.5rem;">
                <span style="display: none;" class="sel-label">Vendedor:</span>
                <svg style="width: 1.5rem; height: 1.5rem; color: #6366f1; flex-shrink: 0;" fill="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="8" r="4" opacity="0.8"/>
                    <path d="M12 14c-4 0-6 2-6 4v3h12v-3c0-2-2-4-6-4Z" opacity="0.8"/>
                    <path d="M19 8c.5 0 1-.5 1-1s-.5-1-1-1-1 .5-1 1 .5 1 1 1Z" opacity="0.6"/>
                    <path d="M20 12c1 0 2-1 2-2v1c0 1.5-1 2.5-2 3" opacity="0.6"/>
                </svg>
                <div style="position: relative; flex: 1;">
                    <select wire:model.live="cajeroId" class="pos-sel" style="flex: 1; padding-right: 2.5rem;" {{ $cajeroLocked ? 'disabled' : '' }}>
                        @foreach($cajeros as $c)
                            <option value="{{ $c->pernr }}">{{ $c->pername }}</option>
                        @endforeach
                    </select>
                    @if($cajeroLocked)
                        <svg style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); width: 1.25rem; height: 1.25rem; color: #6366f1; pointer-events: none;" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                        </svg>
                    @endif
                </div>
            </div>

            {{-- Búsqueda --}}
            <div class="pos-search-wrap">
                <div class="pos-search-icon">
                    <div wire:loading wire:target="search">
                        <svg style="width:1rem;height:1rem;color:#6366f1;animation:spin 1s linear infinite" fill="none" viewBox="0 0 24 24">
                            <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"/>
                        </svg>
                    </div>
                    <div wire:loading.remove wire:target="search">
                        <svg style="width:1rem;height:1rem;color:#9ca3af" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        </svg>
                    </div>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.200ms="search"
                    x-ref="search"
                    autofocus
                    placeholder="Buscar producto..."
                    class="pos-search"
                    @keydown.arrow-down.prevent="arrowDown()"
                    @keydown.arrow-up.prevent="arrowUp()"
                    @keydown.enter.prevent="enter()"
                    @keydown.escape.prevent="clearSearch()"
                />
                <button type="button" class="pos-scan-btn" @click="openScanner()" title="Leer código de barras con la cámara" aria-label="Leer código de barras con la cámara">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75V5.25A1.5 1.5 0 015.25 3.75h1.5M17.25 3.75h1.5a1.5 1.5 0 011.5 1.5v1.5M20.25 17.25v1.5a1.5 1.5 0 01-1.5 1.5h-1.5M6.75 20.25h-1.5a1.5 1.5 0 01-1.5-1.5v-1.5M7.5 8.25v7.5M10.5 8.25v7.5M13.5 8.25v7.5M16.5 8.25v7.5"/></svg>
                </button>
            </div>

                {{-- Categorías (primeras 5) --}}
                <div class="pos-cats">
                    <button type="button" wire:click="$set('categoriaId', null)" class="pos-cat {{ $categoriaId === null ? 'on' : '' }}">
                        Todos
                    </button>
                    @foreach(array_slice($categorias, 0, 5) as $cat)
                        <button type="button" wire:click="$set('categoriaId', '{{ $cat->grupnr }}')" class="pos-cat {{ $categoriaId === $cat->grupnr ? 'on' : '' }}">
                            {{ $cat->descr }}
                        </button>
                    @endforeach
                </div>

                {{-- Grid móvil de productos --}}
                <div class="mobile-dropdown {{ count($searchResults) > 0 ? 'show' : '' }}">
                    <div class="mobile-products-grid">
                        @foreach($searchResults as $i => $product)
                        <div class="mob-item" wire:click="addToCart('{{ $product->item }}')">
                            <img
                                src="{{ route('item-image.show', ['item' => $product->item]) }}"
                                alt="{{ $product->descr }}"
                                loading="lazy"
                                class="mob-item-image"
                                onerror="this.src = 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 stroke=%229ca3af%22 stroke-width=%221.5%22 viewBox=%220 0 24 24%22><path stroke-linecap=%22round%22 stroke-linejoin=%22round%22 d=%22M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z%22/%3E</svg>';"
                            />
                            <div class="mob-item-info">
                                <p class="mob-item-name">{{ $product->descr }}</p>
                                <p class="mob-item-specs">{{ $product->item }}@if($product->scode) · {{ $product->scode }}@endif</p>
                                <div class="mob-item-footer">
                                    <span class="mob-item-stock {{ $product->stock_depo > 5 ? 'high' : ($product->stock_depo > 0 ? 'medium' : 'low') }}">
                                        {{ $product->stock_depo }}
                                    </span>
                                    <p class="mob-item-price">Gs. {{ number_format($product->precio, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <button class="mob-item-add-btn" @click.stop="$wire.addToCart('{{ $product->item }}')">
                                <svg style="width:.8rem;height:.8rem" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
        </div>

        {{-- Grid de productos (solo desktop) --}}
        <div class="pos-products">
            @if(count($searchResults) > 0)
                <div class="products-grid">
                    @foreach($searchResults as $i => $product)
                        <div
                            class="product-card"
                            :class="activeIdx === {{ $i }} ? 'border-indigo-400 shadow-md' : ''"
                            data-result="{{ $i }}"
                            tabindex="0"
                            wire:click="addToCartKeepSearch('{{ $product->item }}')"
                            @keydown.enter.prevent="$wire.addToCartKeepSearch('{{ $product->item }}')"
                            @keydown.arrow-down.prevent="focusResult(activeIdx + 1)"
                            @keydown.arrow-up.prevent="activeIdx > 0 ? focusResult(activeIdx - 1) : (activeIdx = -1, $refs.search.focus())"
                            @keydown.escape.prevent="activeIdx = -1; $refs.search.focus()"
                            @focus="activeIdx = {{ $i }}"
                        >
                            <img
                                src="{{ route('item-image.show', ['item' => $product->item]) }}"
                                alt="{{ $product->descr }}"
                                loading="lazy"
                                class="product-image"
                                onerror="this.classList.add('no-image'); this.src = 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 stroke=%229ca3af%22 stroke-width=%221.5%22 viewBox=%220 0 24 24%22><path stroke-linecap=%22round%22 stroke-linejoin=%22round%22 d=%22M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z%22/%3E</svg>';"
                            />
                            <p style="font-size:.65rem;color:#9ca3af;font-weight:600;">{{ $product->item }}@if($product->scode) · {{ $product->scode }}@endif</p>
                            <p style="font-size:.75rem;font-weight:700;color:#111827;line-height:1.3;flex:1;" class="dark:text-gray-100">{{ $product->descr }}</p>
                            <span style="font-size:.75rem;font-weight:700;padding:.2rem .5rem;border-radius:999px;display:inline-block;width:fit-content;
                                {{ $product->stock_depo > 5 ? 'background:#dcfce7;color:#16a34a;' : ($product->stock_depo > 0 ? 'background:#fef9c3;color:#92400e;' : 'background:#fee2e2;color:#b91c1c;') }}">
                                Stock:&nbsp;{{ $product->stock_depo }}
                            </span>
                            <p style="font-size:1rem;font-weight:900;color:#4f46e5;">Gs.&nbsp;{{ number_format($product->precio, 0, ',', '.') }}</p>
                            <button class="product-add-btn" @click.stop="$wire.addToCartKeepSearch('{{ $product->item }}')">
                                <svg style="width:.9rem;height:.9rem" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                                Agregar
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:#d1d5db;text-align:center;">
                    <svg style="width:3.5rem;height:3.5rem;margin-bottom:1rem" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <p style="font-size:.9rem;color:#9ca3af;">
                        @if(strlen(trim($search)) >= 2)
                            Sin resultados para "{{ $search }}"
                        @else
                            Buscá un producto para empezar
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- ══════ PANEL DERECHO: carrito ══════ --}}
    <div class="pos-cart">

        {{-- Encabezado: Cliente + Total --}}
        <div class="cart-header" style="padding:.5rem .75rem;display:flex;flex-direction:column;gap:.35rem;">
            {{-- Selector de Cliente con búsqueda --}}
            <div x-data="{
                query: '',
                open: false,
                hl: 0,
                selectedName: '',
                selectedId: null,
                closeOnBlur: false,
                get filtered() {
                    const q = this.query.toLowerCase().trim();
                    const clientesList = (this.$wire && this.$wire.clientes) ? this.$wire.clientes : [];
                    if (!q) return clientesList;
                    return clientesList.filter(c => {
                        const name = (c.custname || '').toLowerCase();
                        const ruc = (c.ruc || '').toLowerCase();
                        return name.includes(q) || ruc.includes(q);
                    });
                },
                pick(c) {
                    this.selectedName = c.custname + ' - RUC: ' + (c.ruc || '');
                    this.selectedId = c.custnr;
                    this.query = '';
                    this.open = false;
                    this.$wire.set('clienteId', c.custnr);
                },
                pickHighlighted() {
                    const item = this.filtered[this.hl];
                    if (item) this.pick(item);
                },
                moveDown() {
                    if (!this.open) { this.open = true; return; }
                    this.hl = Math.min(this.hl + 1, this.filtered.length - 1);
                },
                moveUp() {
                    this.hl = Math.max(this.hl - 1, 0);
                },
                close() {
                    if (this.closeOnBlur) {
                        this.open = false;
                        this.query = '';
                    }
                    this.closeOnBlur = false;
                }
            }"
                 x-cloak
                 @click.outside="close()"
                 @cliente-actualizado.window.camel="selectedName = $event.detail.clienteName + ' - RUC: ' + ($event.detail.clienteRuc || ''); selectedId = $event.detail.clienteId; query = ''; open = false"
                 x-effect="if (!selectedId && $wire.clientes && $wire.clientes.length > 0) pick($wire.clientes[0])"
                 style="position:relative;">
                <label style="font-size:.65rem;font-weight:600;color:#6b7280;margin-bottom:.25rem;display:block;">Cliente</label>
                <div style="display:flex;gap:.2rem;align-items:center;">
                    <div style="display:flex;align-items:center;gap:.3rem;flex:1;padding:.5rem .6rem;border:1.5px solid #d1d5db;border-radius:.5rem;background:white;">
                        <svg style="width:1.2rem;height:1.2rem;color:#6b7280;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 1114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                        <input type="text"
                            class="cliente-combo-input"
                            :placeholder="selectedName || 'Cliente...'"
                            x-model="query"
                            @focus="open = true"
                            @input="open = true; hl = 0"
                            @keydown.arrow-down.prevent="moveDown()"
                            @keydown.arrow-up.prevent="moveUp()"
                            @keydown.enter.prevent="pickHighlighted()"
                            @keydown.escape.prevent="close()"
                            autocomplete="off"
                            style="flex:1;border:none;font-size:.75rem;background:white;color:#111827;outline:none;padding:.5rem .4rem;"
                        />
                    </div>
                    <button onclick="window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id')).call('openEditClienteModal')" style="padding:.25rem .3rem;background:#8b5cf6;color:white;border:none;border-radius:.3rem;cursor:pointer;font-size:.6rem;font-weight:700;white-space:nowrap;transition:background .15s;min-width: 1.5rem;display:flex;align-items:center;justify-content:center;" onmouseover="this.style.background='#7c3aed'" onmouseout="this.style.background='#8b5cf6'" title="Editar cliente">
                        <svg style="width:.8rem;height:.8rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <button onclick="window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id')).call('openNewClienteModal')" style="padding:.25rem .3rem;background:#6366f1;color:white;border:none;border-radius:.3rem;cursor:pointer;font-size:.6rem;font-weight:700;white-space:nowrap;transition:background .15s;min-width: 1.5rem;display:flex;align-items:center;justify-content:center;" onmouseover="this.style.background='#4f46e5'" onmouseout="this.style.background='#6366f1'" title="Agregar cliente">
                        +
                    </button>
                </div>
                <div x-show="open" style="display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;z-index:60;background:white;border:1.5px solid #6366f1;border-radius:.5rem;max-height:200px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.1);" class="dark:bg-gray-800">
                    <template x-if="filtered.length > 0">
                        <template x-for="(c, i) in filtered" :key="c.custnr">
                            <div :class="i === hl ? 'bg-violet-100' : ''" style="display:flex;flex-direction:column;gap:.15rem;padding:.4rem .6rem;border-bottom:1px solid #f3f4f6;cursor:pointer;transition:background .08s;" @mouseenter="hl = i" @click="pick(c)">
                                <span style="font-weight:600;color:#111827;font-size:.75rem;" x-text="c.custname" class="dark:text-gray-100"></span>
                                <span style="font-size:.65rem;color:#6b7280;" x-text="'ID: ' + c.custnr"></span>
                                <template x-if="c.ruc">
                                    <span style="font-size:.65rem;color:#6b7280;" x-text="'RUC: ' + c.ruc"></span>
                                </template>
                                <template x-if="c.email">
                                    <span style="font-size:.6rem;color:#9ca3af;" x-text="c.email"></span>
                                </template>
                            </div>
                        </template>
                    </template>
                    <template x-if="filtered.length === 0">
                        <div style="padding:.8rem .6rem;text-align:center;color:#9ca3af;font-size:.75rem;">
                            Sin resultados para "<span x-text="query"></span>"
                        </div>
                    </template>
                </div>
            </div>
            {{-- Total --}}
            <div style="text-align:center;border-top:1.5px solid #e5e7eb;padding-top:.3rem;">
                <div style="font-size:.55rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#0047AB;margin-bottom:.05rem;">TOTAL</div>
                <div style="font-size:1.8rem;font-weight:900;color:#0047AB;line-height:1;display:flex;align-items:center;justify-content:center;gap:.6rem;">
                    <span style="font-size:1.5rem;">🛒</span>
                    Gs.&nbsp;{{ number_format($this->getCartTotal(), 0, ',', '.') }}
                </div>
            </div>
        </div>

        {{-- Ítems del carrito --}}
        <div class="cart-body">
            @if(empty($cart))
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;padding:2rem;text-align:center;color:#d1d5db;">
                    <svg style="width:3rem;height:3rem;margin-bottom:.75rem" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/>
                    </svg>
                    <p style="font-size:.85rem;color:#9ca3af;">Carrito vacío</p>
                    <p style="font-size:.72rem;color:#d1d5db;margin-top:.25rem;">Buscá un producto a la izquierda</p>
                </div>
            @else
                @foreach($cart as $i => $item)
                    <div class="cart-row"
                        data-cart-idx="{{ $i }}"
                        tabindex="0"
                        @click="openEdit({{ $i }}, {{ $item['qty'] }}, {{ $item['precio'] }}, '{{ addslashes($item['descr']) }}', {{ $item['stock_depo'] }})"
                        @keydown.enter.prevent="openEdit({{ $i }}, {{ $item['qty'] }}, {{ $item['precio'] }}, '{{ addslashes($item['descr']) }}', {{ $item['stock_depo'] }})"
                        @keydown.delete.prevent="$wire.removeFromCart({{ $i }})"
                        @keydown.arrow-down.prevent="focusCartItem({{ $i + 1 }})"
                        @keydown.arrow-up.prevent="{{ $i === 0 ? '$refs.search.focus()' : 'focusCartItem(' . ($i - 1) . ')' }}"
                    >
                        {{-- Imagen del producto --}}
                        <img
                            src="{{ route('item-image.show', ['item' => $item['item']]) }}"
                            alt="{{ $item['descr'] }}"
                            class="cart-item-image"
                            loading="lazy"
                            onerror="this.src = 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 stroke=%229ca3af%22 stroke-width=%221.5%22 viewBox=%220 0 24 24%22><path stroke-linecap=%22round%22 stroke-linejoin=%22round%22 d=%22M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z%22/%3E</svg>';"
                        />

                        {{-- Información del producto --}}
                        <div style="flex:1;min-width:0;">
                            <p style="font-size:.82rem;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" class="dark:text-gray-100">{{ $item['descr'] }}</p>
                            <p style="font-size:.68rem;color:#9ca3af;">{{ $item['item'] }} · x{{ $item['qty'] }}</p>
                        </div>

                        {{-- Precio --}}
                        <div style="text-align:right;flex-shrink:0;">
                            <p style="font-size:.82rem;font-weight:800;color:#4f46e5;">Gs.&nbsp;{{ number_format($item['precio'] * $item['qty'], 0, ',', '.') }}</p>
                            <p style="font-size:.68rem;color:#9ca3af;">c/u&nbsp;{{ number_format($item['precio'], 0, ',', '.') }}</p>
                        </div>

                        {{-- Botón eliminar --}}
                        <button class="del-btn" @click.stop="$wire.removeFromCart({{ $i }})" title="Eliminar">
                            <svg style="width:.8rem;height:.8rem" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Resumen: cantidad de items y total (visible en desktop/tablet) --}}
        <div class="cart-summary">
            <span class="cart-summary-count">{{ count($cart) }} {{ count($cart) === 1 ? 'artículo' : 'artículos' }}</span>
            <span class="cart-summary-total">Gs. {{ number_format($this->getCartTotal(), 0, ',', '.') }}</span>
        </div>

        {{-- Footer: botones --}}
        <div class="cart-footer" style="gap:.5rem;" id="cart-footer-full">
            {{-- Guardar (sin acción por ahora) --}}
            <button type="button" {{ empty($cart) ? 'disabled' : '' }}
                style="display:flex;align-items:center;justify-content:center;flex-direction:column;gap:.25rem;padding:.5rem .7rem;
                    background:#f3f4f6;color:#4b5563;font-weight:700;font-size:.75rem;
                    border:1.5px solid #d1d5db;border-radius:.75rem;cursor:pointer;
                    transition:all .15s;white-space:nowrap;flex:1;"
                onmouseover="if(!this.disabled)this.style.background='#e5e7eb'"
                onmouseout="this.style.background='#f3f4f6'">
                <svg style="width:.9rem;height:.9rem" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"/>
                </svg>
                Guardar
            </button>

            {{-- Cobrar --}}
            <button wire:click="openPaymentModal" class="cobrar-btn" style="flex:1;flex-direction:column;justify-content:center;gap:.25rem;padding:.5rem .7rem;display:flex;align-items:center;font-size:.75rem;" {{ empty($cart) ? 'disabled' : '' }}>
                <svg style="width:.9rem;height:.9rem" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                </svg>
                <div style="display:flex;gap:.3rem;align-items:center;">
                    <span style="font-size:.75rem;font-weight:900;">@if(auth()->user()?->empresa?->modulo_caja_enabled ?? true) COBRAR @else Generar Venta @endif</span>
                    <span style="font-size:.65rem;font-weight:600;opacity:.85;">F10</span>
                </div>
            </button>
        </div>

        {{-- Footer compacto: resumen + botones en dos filas --}}
        <div class="cart-footer-compact" style="display:none;flex-direction:column;padding:.4rem .5rem;background:white;border-top:1px solid #e5e7eb;gap:.3rem;flex-shrink:0;" id="cart-footer-compact">
            {{-- Fila 1: Cantidad y Total --}}
            <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;">
                <span style="font-size:.7rem;color:#6b7280;font-weight:600;white-space:nowrap;">{{ count($cart) }} producto{{ count($cart) !== 1 ? 's' : '' }}</span>
                <div style="display:flex;align-items:baseline;gap:.3rem;">
                    <span style="font-size:.65rem;color:#6b7280;font-weight:600;">Total</span>
                    <span style="font-size:1.2rem;font-weight:900;color:#0047AB;">Gs.&nbsp;{{ number_format($this->getCartTotal(), 0, ',', '.') }}</span>
                </div>
            </div>
            {{-- Fila 2: Botones --}}
            <div style="display:flex;gap:.3rem;width:100%;">
                <button type="button" {{ empty($cart) ? 'disabled' : '' }}
                    style="display:flex;align-items:center;justify-content:center;padding:.4rem;flex:1;
                        background:#f3f4f6;color:#4b5563;font-weight:700;font-size:.65rem;
                        border:1px solid #d1d5db;border-radius:.5rem;cursor:pointer;
                        transition:all .15s;height:2.4rem;gap:.25rem;"
                    onmouseover="if(!this.disabled)this.style.background='#e5e7eb'"
                    onmouseout="this.style.background='#f3f4f6'">
                    <svg style="width:.7rem;height:.7rem" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"/>
                    </svg>
                    <span>Guardar</span>
                </button>
                <button wire:click="openPaymentModal" class="cobrar-btn" style="display:flex;align-items:center;justify-content:center;padding:.4rem;flex:1.2;font-size:.65rem;font-weight:700;gap:.25rem;height:2.4rem;" {{ empty($cart) ? 'disabled' : '' }}>
                    <svg style="width:.7rem;height:.7rem" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                    </svg>
                    <span>@if(auth()->user()?->empresa?->modulo_caja_enabled ?? true) COBRAR @else Generar Venta @endif</span>
                </button>
            </div>
        </div>

    </div>{{-- /pos-cart --}}

</div>{{-- /pos-wrap --}}


{{-- ══════════════ MODAL: EDITAR CANTIDAD Y PRECIO ══════════════ --}}
<div x-show="edit.open" class="modal-back" @click.self="cancelEdit()" @keydown.escape.window="cancelEdit()" style="display:none;">
    <div class="modal-box">
        <p style="font-size:.95rem;font-weight:800;color:#111827;margin-bottom:1rem;" class="dark:text-white" x-text="edit.descr"></p>

        <label style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#6b7280;display:block;margin-bottom:.4rem;">Cantidad</label>
        <div class="qty-ctrl">
            <button class="qty-btn" @click="if(edit.qty > 1) edit.qty--">−</button>
            <input class="qty-num" type="number" min="1" x-model.number="edit.qty" @change="if(edit.qty > edit.stock_depo && edit.stock_depo > 0) { edit.qty = edit.stock_depo }" />
            <button class="qty-btn" @click="if(edit.qty < edit.stock_depo) { edit.qty++ } else if(edit.stock_depo > 0) { $dispatch('notify', {type: 'warning', title: 'Stock insuficiente', message: 'No hay suficiente stock disponible'}) }">+</button>
        </div>

        <label style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#6b7280;display:block;margin-bottom:.4rem;">Precio unitario (Gs.)</label>
        <input
            id="edit-precio"
            type="number" min="0"
            x-model.number="edit.precio"
            style="width:100%;padding:.75rem;border-radius:.8rem;border:2px solid #6366f1;font-size:1.4rem;font-weight:800;text-align:center;color:#111827;background:white;outline:none;box-shadow:0 0 0 3px rgba(99,102,241,.12);margin-bottom:.85rem;"
            @focus="$event.target.select()"
            @keydown.enter.prevent="saveEdit()"
        />

        <div style="background:#f5f3ff;border-radius:.7rem;padding:.6rem 1rem;display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <span style="font-size:.78rem;color:#7c3aed;font-weight:600;">Subtotal (x<span x-text="edit.qty"></span>):</span>
            <span style="font-size:1.05rem;font-weight:900;color:#4f46e5;" x-text="'Gs. ' + (edit.qty * edit.precio).toLocaleString('es-PY')"></span>
        </div>

        <div style="display:flex;gap:.65rem;">
            <button @click="cancelEdit()" style="flex:1;padding:.7rem;border-radius:.8rem;border:1.5px solid #e5e7eb;background:transparent;font-weight:700;color:#6b7280;cursor:pointer;font-size:.88rem;">Cancelar</button>
            <button @click="saveEdit()" style="flex:1.5;padding:.7rem;border-radius:.8rem;background:#4f46e5;color:white;font-weight:900;font-size:.95rem;border:none;cursor:pointer;box-shadow:0 3px 10px rgba(79,70,229,.3);">Guardar</button>
        </div>
    </div>
</div>

{{-- ══════════════ MODAL: COBRO ══════════════ --}}
@if($showPaymentModal && !$lastInvoiceNumber)
<div class="modal-back" @keydown.escape.window="$wire.closePaymentModal()" @keydown.enter.window="$wire.set('amountReceived', localAmount).then(() => $wire.confirmSale())" wire:watch="lastInvoiceNumber">
    <div class="modal-box" style="max-width:550px;" @click.stop>
        <div style="background:linear-gradient(135deg,#4f46e5,#7c3aed);margin:-1.4rem -1.4rem 1.1rem;padding:1.1rem 1.35rem;border-radius:.85rem .85rem 0 0;">
            <p style="font-size:1rem;font-weight:900;color:white;">Cobrar Venta</p>
            <p style="font-size:.75rem;color:rgba(255,255,255,.65);margin-top:.1rem;">Seleccioná la forma de pago</p>
        </div>

        <div style="background:#f8fafc;border-radius:.8rem;padding:.85rem;text-align:center;margin-bottom:.9rem;" class="pm-total dark:bg-gray-700">
            <p style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;">Total</p>
            <p style="font-size:1.9rem;font-weight:900;color:#111827;" class="dark:text-white">Gs.&nbsp;{{ number_format($this->getCartTotal(), 0, ',', '.') }}</p>
        </div>

        {{-- ANTES DE CONFIRMAR --}}
        @if(!$lastInvoiceNumber)
            <p style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;margin-bottom:.45rem;">Forma de pago</p>
            <div style="display:flex;gap:.5rem;margin-bottom:.9rem;">
                <button wire:click="$set('paymentMethod','efectivo')" class="pay-method {{ $paymentMethod==='efectivo'?'sel-green':'' }}">
                    <span class="pm-ico"><svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg></span>
                    <span>Efectivo</span>
                </button>
                <button wire:click="$set('paymentMethod','transferencia')" class="pay-method {{ $paymentMethod==='transferencia'?'sel-blue':'' }}">
                    <span class="pm-ico"><svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/></svg></span>
                    <span>Transferencia</span>
                </button>
                <button wire:click="$set('paymentMethod','tarjeta')" class="pay-method {{ $paymentMethod==='tarjeta'?'sel-purple':'' }}">
                    <span class="pm-ico"><svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg></span>
                    <span>Tarjeta</span>
                </button>
            </div>

            @if($paymentMethod === 'efectivo')
            <div style="background:#f0fdf4;border-radius:.8rem;padding:.85rem;margin-bottom:.9rem;" class="pm-monto">
                <label style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#16a34a;display:block;margin-bottom:.35rem;">Monto recibido</label>
                <input id="amount-input" type="number"
                    data-total="{{ (float) $this->getCartTotal() }}"
                    oninput="clearTimeout(window._vueltoT); const el=this; window._vueltoT=setTimeout(()=>{ const t=parseFloat(el.dataset.total)||0, m=parseFloat(el.value)||0, v=document.getElementById('vuelto-text'); if(!v) return; v.textContent='Gs. '+new Intl.NumberFormat('es-PY').format(Math.max(0,m-t)); v.style.color = m>=t ? '#16a34a' : '#dc2626'; }, 600)"
                    x-init="localAmount = {{ (float) $this->getCartTotal() }}; $nextTick(() => { $el.focus(); $el.select(); })"
                    x-model.number="localAmount"
                    style="width:100%;padding:.6rem;border-radius:.7rem;border:1.5px solid #86efac;background:white;font-size:1.25rem;font-weight:800;text-align:center;outline:none;"
                    @focus="$event.target.select()"
                    @keydown.enter.prevent="$wire.set('amountReceived', localAmount).then(() => $wire.confirmSale())" />
                @php $cartTotal = $this->getCartTotal(); @endphp
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.5rem;">
                    <p style="font-size:.8rem;font-weight:600;color:#166534;">Vuelto:</p>
                    <p id="vuelto-text" style="font-size:1.35rem;font-weight:900;color:#16a34a;">Gs. 0</p>
                </div>
            </div>
            @endif

            <div style="display:flex;gap:.65rem;">
                <button wire:click="closePaymentModal" class="pm-cancel" style="flex:1;padding:.7rem;border-radius:.8rem;border:1.5px solid #e5e7eb;background:transparent;font-weight:700;color:#6b7280;cursor:pointer;">Cancelar</button>
                <button @click="$wire.set('amountReceived', localAmount).then(() => $wire.confirmSale())"
                    class="pm-confirm"
                    wire:loading.attr="disabled" wire:target="confirmSale"
                    style="flex:1.5;padding:.7rem;border-radius:.8rem;background:#4f46e5;color:white;font-weight:900;font-size:.95rem;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.4rem;box-shadow:0 4px 14px rgba(79,70,229,.35);">
                    <svg style="width:1rem;height:1rem" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span wire:loading.remove wire:target="confirmSale">Confirmar</span>
                    <span wire:loading wire:target="confirmSale">Procesando...</span>
                </button>
            </div>
        @endif

    </div>
</div>
@endif

{{-- ══════════════ MODAL: ¿IMPRIMIR TICKET? ══════════════ --}}
@if($showPaymentModal && $lastInvoiceNumber)
@php
    $vueltoVenta = $lastPaymentMethod === 'efectivo' ? max(0, $lastReceived - $lastTotal) : 0;
    $ticketUrl = url('/comprobante') . '/' . $lastInvoiceNumber . '/ticket?pago=' . (int) $lastReceived . '&medio=' . $lastPaymentMethod;
@endphp
<div class="modal-back"
     x-data="{ imprimir() { window.open('{{ $ticketUrl }}', '_blank'); $wire.closePaymentModal(); } }"
     x-init="$nextTick(() => $refs.btnImprimir?.focus())"
     @keydown.enter.window.prevent="imprimir()"
     @keydown.escape.window="$wire.closePaymentModal()">
    <div class="modal-box" style="max-width:420px;text-align:center;" @click.stop>
        <div style="width:3.4rem;height:3.4rem;margin:0 auto .8rem;border-radius:9999px;background:#dcfce7;display:flex;align-items:center;justify-content:center;">
            <svg style="width:1.9rem;height:1.9rem;color:#16a34a;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
        </div>
        <p style="font-size:1.1rem;font-weight:900;color:#111827;" class="dark:text-white">Venta registrada</p>
        <p style="font-size:.78rem;color:#6b7280;margin-top:.15rem;">Ticket Nº {{ str_pad($lastInvoiceNumber, 6, '0', STR_PAD_LEFT) }} · Gs. {{ number_format($lastTotal, 0, ',', '.') }}</p>

        @if($vueltoVenta > 0)
        <div style="margin:.9rem 0 .2rem;background:#f0fdf4;border-radius:.8rem;padding:.65rem;">
            <p style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#16a34a;">Vuelto</p>
            <p style="font-size:1.7rem;font-weight:900;color:#16a34a;">Gs. {{ number_format($vueltoVenta, 0, ',', '.') }}</p>
        </div>
        @endif

        <p style="font-size:.95rem;font-weight:700;color:#374151;margin:1rem 0 .8rem;" class="dark:text-gray-200">¿Desea imprimir el ticket?</p>

        <div style="display:flex;gap:.65rem;">
            <button wire:click="closePaymentModal" style="flex:1;padding:.7rem;border-radius:.8rem;border:1.5px solid #e5e7eb;background:transparent;font-weight:700;color:#6b7280;cursor:pointer;">No</button>
            <button x-ref="btnImprimir" @click="imprimir()" style="flex:1.5;padding:.7rem;border-radius:.8rem;background:#4f46e5;color:white;font-weight:900;font-size:.95rem;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.45rem;box-shadow:0 4px 14px rgba(79,70,229,.35);">
                <svg style="width:1.05rem;height:1.05rem" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
                Sí, imprimir
            </button>
        </div>
        <p style="font-size:.65rem;color:#9ca3af;margin-top:.7rem;">Enter = imprimir · Esc = no imprimir</p>
    </div>
</div>
@endif

{{-- ══════════════ MODAL: LECTOR DE CÓDIGO DE BARRAS (CÁMARA) ══════════════ --}}
<div x-show="scannerOpen" x-cloak style="display:none;" class="modal-back" @keydown.escape.window="scannerOpen && closeScanner()" @click.self="closeScanner()">
    <div class="modal-box scan-box" style="max-width:480px;" @click.stop>
        <div class="scan-head">
            <div>
                <p class="scan-title">Leer código de barras</p>
                <p class="scan-sub">Apuntá la cámara al código del producto</p>
            </div>
            <button type="button" class="scan-close" @click="closeScanner()" aria-label="Cerrar">
                <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="scan-view" wire:ignore>
            <div id="barcode-reader"></div>
        </div>

        <p x-show="scannerStatus && !scannerError" x-text="scannerStatus" class="scan-status"></p>
        <p x-show="scannerError" x-text="scannerError" class="scan-error"></p>

        <button type="button" class="scan-cancel" @click="closeScanner()">Cancelar</button>
    </div>
</div>

</div>{{-- /x-data posApp --}}

<script>
function posApp() {
    return {
        activeIdx: -1,
        isFullscreen: false,
        edit: { open: false, idx: null, qty: 1, precio: 0, descr: '', stock_depo: 0 },
        localAmount: 0,
        calcAmount: 0,
        cartKey: '',
        scannerOpen: false,
        scannerStatus: '',
        scannerError: '',
        _scanner: null,
        keyboardVisible: false,
        lastViewportHeight: 0,

        init() {
            this.$watch('$wire.searchResults', () => { this.activeIdx = -1; });

            // Persistencia del carrito en el navegador: sobrevive a recargas y cierres accidentales
            this.cartKey = this.$el.dataset.cartKey;
            this.$watch('$wire.cart', () => this.persistCart());
            this.$watch('$wire.clienteId', () => this.persistCart());
            this.$watch('$wire.depositoId', () => this.persistCart());
            const restoreTimer = setTimeout(() => this.restoreSavedCart(), 700);
            this._restoreTimer = restoreTimer;

            // Auto-scroll al carrito cuando se agrega un producto
            this.$watch('$wire.cart', () => {
                this.$nextTick(() => {
                    const cartBody = document.querySelector('.cart-body');
                    if (cartBody) {
                        cartBody.scrollTop = cartBody.scrollHeight;
                    }
                });
            });

            const self = this;

            // Con navegación SPA la página no se recarga: todo listener global debe poder quitarse en destroy()
            this._cleanups = [];
            const on = (target, evt, fn) => {
                target.addEventListener(evt, fn);
                this._cleanups.push(() => target.removeEventListener(evt, fn));
            };
            on(document, 'livewire:navigating', () => this.destroy());

            // F10 en el input de búsqueda para abrir modal de cobro
            this.$nextTick(() => {
                const searchInput = document.querySelector('input[placeholder*="Buscar"]');
                if (searchInput) {
                    searchInput.addEventListener('keydown', function(e) {
                        if (e.key === 'F10') {
                            e.preventDefault();
                            const buttons = document.querySelectorAll('button');
                            for (let btn of buttons) {
                                if (btn.textContent.includes('COBRAR')) {
                                    btn.click();
                                    break;
                                }
                            }
                        }
                    }.bind(this));
                }
            });

            on(window, 'keydown', (e) => {
                if (e.key === 'F11') {
                    e.preventDefault();
                    self.toggleFullscreen();
                }
            });
            on(window, 'toggle-fullscreen', () => this.toggleFullscreen());

            // Listener para evento de fullscreen de Livewire
            if (this.$wire) {
                this.$wire.on('toggleFullscreenMode', () => {
                    this.toggleFullscreen();
                });
            }

            // Auto-cargar total cuando se abre el modal de pago
            this.$watch('$wire.showPaymentModal', (isOpen) => {
                if (isOpen) {
                    this.$nextTick(async () => {
                        try {
                            // Obtener el total desde Livewire
                            const cartTotal = await this.$wire.call('getCartTotal');
                            this.localAmount = cartTotal;
                        } catch (e) {
                            console.error('Error getting cart total:', e);
                        }
                        const el = document.getElementById('amount-input');
                        if (el) {
                            this.$nextTick(() => {
                                el.focus();
                                el.select();
                            });
                        }
                    });
                }
            });

            on(window, 'focus-amount', () => {
                this.$nextTick(() => {
                    this.localAmount = this.$wire.amountReceived;
                    const el = document.getElementById('amount-input');
                    el?.focus(); el?.select();
                });
            });
            on(window, 'beforeunload', () => document.body.classList.remove('pos-fullscreen'));

            // Detectar teclado virtual en móviles usando visualViewport
            if (window.visualViewport) {
                on(window.visualViewport, 'resize', () => this.handleViewportChange());
                on(window.visualViewport, 'scroll', () => this.handleViewportChange());
                this.lastViewportHeight = window.visualViewport.height;
            }

            // Fallback: detectar cambios en innerHeight
            on(window, 'resize', () => this.handleViewportChange());

            // Activar pantalla completa automáticamente después de cargar
            const fsTimer = setTimeout(() => {
                try { if (!self.isFullscreen) self.toggleFullscreen(); } catch(e) { console.log('toggleFullscreen error:', e); }
            }, 1000);
            this._cleanups.push(() => clearTimeout(fsTimer));
        },

        /* ─── Carrito guardado en el navegador ─── */
        persistCart() {
            if (!this.cartKey) return;
            try {
                const cart = this.$wire.cart || [];
                if (!cart.length) {
                    localStorage.removeItem(this.cartKey);
                    return;
                }
                localStorage.setItem(this.cartKey, JSON.stringify({
                    ts: Date.now(),
                    cart: JSON.parse(JSON.stringify(cart)),
                    clienteId: this.$wire.clienteId ?? null,
                    depositoId: this.$wire.depositoId ?? null,
                }));
            } catch (e) {}
        },

        restoreSavedCart() {
            if (!this.cartKey) return;
            try {
                if ((this.$wire.cart || []).length > 0) return;
                const raw = localStorage.getItem(this.cartKey);
                if (!raw) return;
                const saved = JSON.parse(raw);
                const vencido = !saved.ts || (Date.now() - saved.ts) > 24 * 60 * 60 * 1000;
                if (vencido || !Array.isArray(saved.cart) || saved.cart.length === 0) {
                    localStorage.removeItem(this.cartKey);
                    return;
                }
                this.$wire.restoreCart(saved.cart, saved.clienteId ?? null, saved.depositoId ?? null);
            } catch (e) {
                try { localStorage.removeItem(this.cartKey); } catch (e2) {}
            }
        },

        destroy() {
            clearTimeout(this._restoreTimer);
            (this._cleanups || []).forEach(fn => fn());
            this._cleanups = [];
            document.body.classList.remove('pos-fullscreen', 'pos-keyboard-visible');
            this.stopScanner();
        },

        /* ─── Lector de código de barras con cámara ─── */
        loadScannerLib() {
            if (window.Html5Qrcode) return Promise.resolve();
            if (window._html5qrLoading) return window._html5qrLoading;
            window._html5qrLoading = new Promise((resolve, reject) => {
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js';
                s.onload = () => resolve();
                s.onerror = () => { window._html5qrLoading = null; reject(new Error('No se pudo cargar el lector. Verificá tu conexión a internet.')); };
                document.head.appendChild(s);
            });
            return window._html5qrLoading;
        },

        async openScanner() {
            this.scannerError = '';
            this.scannerStatus = 'Iniciando cámara...';
            this.scannerOpen = true;
            await this.$nextTick();

            if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
                this.scannerError = 'La cámara solo funciona en una conexión segura (https) y con un navegador compatible.';
                return;
            }

            try {
                await this.loadScannerLib();
                const F = window.Html5QrcodeSupportedFormats;
                this._scanner = new Html5Qrcode('barcode-reader', {
                    formatsToSupport: [F.EAN_13, F.EAN_8, F.UPC_A, F.UPC_E, F.CODE_128, F.CODE_39, F.CODE_93, F.ITF, F.QR_CODE],
                    experimentalFeatures: { useBarCodeDetectorIfSupported: true },
                    verbose: false,
                });
                await this._scanner.start(
                    { facingMode: 'environment' },
                    {
                        fps: 15,
                        disableFlip: true,
                        // Área de lectura ancha: los códigos de barras son horizontales y largos
                        qrbox: (w, h) => ({ width: Math.floor(w * 0.92), height: Math.floor(Math.min(h * 0.7, w * 0.55)) }),
                        // Alta resolución: con 640x480 el código queda con muy pocos píxeles y no se decodifica
                        videoConstraints: {
                            facingMode: { ideal: 'environment' },
                            width: { ideal: 1920 },
                            height: { ideal: 1080 },
                        },
                    },
                    (text) => this.onScanSuccess(text),
                    () => {}
                );
                // Enfoque continuo si la cámara lo soporta (si no, se ignora)
                try { await this._scanner.applyVideoConstraints({ focusMode: 'continuous' }); } catch (e) {}
                this.scannerStatus = 'Acercá el código, con buena luz, y mantenelo quieto.';
            } catch (e) {
                this.scannerStatus = '';
                const msg = String(e?.message || e);
                this.scannerError = /Permission|NotAllowed|denied/i.test(msg)
                    ? 'No se permitió el acceso a la cámara. Habilitá el permiso en el navegador e intentá de nuevo.'
                    : /NotFound|no camera|Requested device not found/i.test(msg)
                        ? 'No se encontró ninguna cámara en este dispositivo.'
                        : (msg || 'No se pudo iniciar la cámara.');
                this.stopScanner();
            }
        },

        onScanSuccess(text) {
            if (!text) return;
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                osc.frequency.value = 880;
                osc.connect(ctx.destination);
                osc.start();
                setTimeout(() => { osc.stop(); ctx.close(); }, 120);
            } catch (e) {}
            if (navigator.vibrate) navigator.vibrate(60);
            this.closeScanner();
            this.$wire.set('search', String(text).trim());
        },

        async stopScanner() {
            const sc = this._scanner;
            this._scanner = null;
            if (!sc) return;
            try { await sc.stop(); } catch (e) {}
            try { sc.clear(); } catch (e) {}
        },

        async closeScanner() {
            this.scannerOpen = false;
            this.scannerStatus = '';
            this.scannerError = '';
            await this.stopScanner();
            this.$nextTick(() => this.$refs.search?.focus());
        },

        handleViewportChange() {
            const currentHeight = window.visualViewport?.height || window.innerHeight;
            const heightRatio = currentHeight / (window.visualViewport?.scale || 1) / window.screen.height;

            // Si la altura visible es menos del 70% de la altura de pantalla, probablemente el teclado está abierto
            if (heightRatio < 0.7 && !this.keyboardVisible) {
                this.keyboardVisible = true;
                document.body.classList.add('pos-keyboard-visible');
            } else if (heightRatio >= 0.7 && this.keyboardVisible) {
                this.keyboardVisible = false;
                document.body.classList.remove('pos-keyboard-visible');
            }
        },

        arrowDown() {
            const results = document.querySelectorAll('[data-result]');
            if (results.length > 0) {
                if (this.activeIdx < results.length - 1) {
                    this.activeIdx++;
                    this.$nextTick(() => results[this.activeIdx]?.scrollIntoView({ block: 'nearest' }));
                }
            } else { this.focusCartItem(0); }
        },
        arrowUp() {
            if (this.activeIdx > 0) this.activeIdx--;
            else this.activeIdx = -1;
        },
        enter() {
            const results = document.querySelectorAll('[data-result]');
            if (this.activeIdx >= 0 && results[this.activeIdx]) {
                // Usar addToCartKeepSearch para mantener el grid visible
                const item = results[this.activeIdx].getAttribute('wire:click')?.match(/'([^']+)'/)?.[1];
                if (item) this.$wire.addToCartKeepSearch(item);
            } else if (results.length === 1) {
                const item = results[0].getAttribute('wire:click')?.match(/'([^']+)'/)?.[1];
                if (item) this.$wire.addToCartKeepSearch(item);
            } else if (results.length > 1) {
                this.focusResult(0);
            }
        },
        focusResult(idx) {
            const results = document.querySelectorAll('[data-result]');
            if (results[idx]) { this.activeIdx = idx; results[idx].focus(); results[idx].scrollIntoView({ block: 'nearest' }); }
        },
        focusCartItem(idx) {
            const items = document.querySelectorAll('[data-cart-idx]');
            if (items[idx]) { items[idx].focus(); items[idx].scrollIntoView({ block: 'nearest' }); }
        },
        clearSearch() {
            this.$wire.set('search', ''); this.activeIdx = -1; this.$refs.search?.focus();
        },

        openEdit(idx, qty, precio, descr, stock_depo) {
            this.edit = { open: true, idx, qty: parseInt(qty), precio: parseFloat(precio), descr, stock_depo: parseInt(stock_depo) };
            this.$nextTick(() => document.getElementById('edit-precio')?.focus());
        },
        saveEdit() {
            // Validar que el precio no esté por debajo del mínimo permitido
            this.$wire.validateAndUpdateCartItem(this.edit.idx, this.edit.qty, this.edit.precio);
            this.edit.open = false;
            this.$nextTick(() => this.$refs.search?.focus());
        },
        cancelEdit() {
            this.edit.open = false;
            this.$nextTick(() => this.$refs.search?.focus());
        },

        toggleFullscreen() {
            this.isFullscreen = !this.isFullscreen;
            if (this.isFullscreen) {
                document.body.classList.add('pos-fullscreen');
                // Intentar la API de fullscreen si está disponible
                try {
                    document.documentElement.requestFullscreen?.()?.catch(() => {});
                } catch (e) {
                    // Fallar silenciosamente si no está disponible
                }
            } else {
                document.body.classList.remove('pos-fullscreen');
                try {
                    document.exitFullscreen?.();
                } catch (e) {
                    // Fallar silenciosamente
                }
            }
            this.$nextTick(() => this.$refs.search?.focus());
        },
    };
}

function posClienteCombo() {
    return {
        query: '', open: false, hl: 0, selectedName: '', selectedId: null, closeOnBlur: false,
        get filtered() {
            const q = this.query.toLowerCase().trim();
            // Obtener clientes desde Livewire
            const clientesList = (this.$wire && this.$wire.clientes) ? this.$wire.clientes : [];
            if (!q) return clientesList;

            // Filtrar solo por nombre y RUC
            return clientesList.filter(c => {
                const name = (c.custname || '').toLowerCase();
                const ruc = (c.ruc || '').toLowerCase();
                return name.includes(q) || ruc.includes(q);
            });
        },
        pick(c) {
            this.selectedName = `${c.custname} (${c.custnr})${c.ruc ? ' - RUC: ' + c.ruc : ''}`;
            this.selectedId = c.custnr;
            this.query = ''; this.open = false;
            this.$wire.set('clienteId', c.custnr);
            this.$nextTick(() => window.dispatchEvent(new CustomEvent('focus-search')));
        },
        pickHighlighted() { const item = this.filtered[this.hl]; if (item) this.pick(item); },
        moveDown() {
            if (!this.open) { this.open = true; return; }
            this.hl = Math.min(this.hl + 1, this.filtered.length - 1);
            this.$nextTick(() => this.$el.querySelector(`[data-pci="${this.hl}"]`)?.scrollIntoView({ block: 'nearest' }));
        },
        moveUp() {
            this.hl = Math.max(this.hl - 1, 0);
            this.$nextTick(() => this.$el.querySelector(`[data-pci="${this.hl}"]`)?.scrollIntoView({ block: 'nearest' }));
        },
        close() {
            if (this.closeOnBlur) {
                this.open = false;
                this.query = '';
            }
            this.closeOnBlur = false;
        },
    };
}
</script>

{{-- ══════════════ MODAL: NUEVO CLIENTE ══════════════ --}}
@if($showNewClienteModal)
<div class="modal-back" @keydown.escape.window="$wire.call('closeNewClienteModal')" @click.self="$wire.call('closeNewClienteModal')">
    <div class="modal-box" style="max-width:650px;" @click.stop>
        <div style="background:linear-gradient(135deg,#4f46e5,#7c3aed);margin:-1.4rem -1.4rem 1.1rem;padding:1.1rem 1.35rem;border-radius:.85rem .85rem 0 0;text-align:center;">
            <p style="font-size:1rem;font-weight:900;color:white;">@if($editingClienteId) Editar Cliente @else Nuevo Cliente @endif</p>
            <p style="font-size:.75rem;color:rgba(255,255,255,.65);margin-top:.1rem;">@if($editingClienteId) Modificar información @else Registro rápido @endif</p>
        </div>

        <div style="display:flex;flex-direction:column;gap:.6rem;max-height:60vh;overflow-y:auto;">
            <div>
                <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;display:block;margin-bottom:.25rem;">Nombre *</label>
                <input wire:model="newClienteName" type="text" placeholder="Nombre completo" style="width:100%;padding:.5rem;border:1.5px solid #d1d5db;border-radius:.5rem;font-size:.8rem;outline:none;transition:border-color .2s;" @focus="$el.style.borderColor='#6366f1'" @blur="$el.style.borderColor='#d1d5db'"/>
            </div>

            <div>
                <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;display:block;margin-bottom:.25rem;">Nacionalidad</label>
                <select wire:model="newClienteNacionalidad" style="width:100%;padding:.5rem;border:1.5px solid #d1d5db;border-radius:.5rem;font-size:.8rem;outline:none;background:white;" @focus="$el.style.borderColor='#6366f1'" @blur="$el.style.borderColor='#d1d5db'">
                    <option value="">Seleccionar país...</option>
                    <option value="PY">Paraguay</option>
                    <option value="AR">Argentina</option>
                    <option value="BR">Brasil</option>
                    <option value="CL">Chile</option>
                    <option value="UY">Uruguay</option>
                    <option value="BO">Bolivia</option>
                    <option value="CO">Colombia</option>
                    <option value="PE">Perú</option>
                    <option value="EC">Ecuador</option>
                    <option value="VE">Venezuela</option>
                    <option value="GY">Guyana</option>
                    <option value="SR">Surinam</option>
                </select>
            </div>

            <div>
                <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;display:block;margin-bottom:.25rem;">Dirección</label>
                <input wire:model="newClienteDireccion" type="text" placeholder="Dirección..." style="width:100%;padding:.5rem;border:1.5px solid #d1d5db;border-radius:.5rem;font-size:.8rem;outline:none;transition:border-color .2s;" @focus="$el.style.borderColor='#6366f1'" @blur="$el.style.borderColor='#d1d5db'"/>
            </div>

            <div>
                <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;display:block;margin-bottom:.25rem;">Teléfono</label>
                <input wire:model="newClienteTelefono" type="tel" placeholder="Teléfono..." style="width:100%;padding:.5rem;border:1.5px solid #d1d5db;border-radius:.5rem;font-size:.8rem;outline:none;transition:border-color .2s;" @focus="$el.style.borderColor='#6366f1'" @blur="$el.style.borderColor='#d1d5db'"/>
            </div>

            <div>
                <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;display:block;margin-bottom:.25rem;">Correo</label>
                <input wire:model="newClienteEmail" type="email" placeholder="correo@ejemplo.com" style="width:100%;padding:.5rem;border:1.5px solid #d1d5db;border-radius:.5rem;font-size:.8rem;outline:none;transition:border-color .2s;" @focus="$el.style.borderColor='#6366f1'" @blur="$el.style.borderColor='#d1d5db'"/>
            </div>

            <div>
                <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;display:block;margin-bottom:.25rem;">RUC</label>
                <input wire:model="newClienteRuc" type="text" placeholder="RUC..." style="width:100%;padding:.5rem;border:1.5px solid #d1d5db;border-radius:.5rem;font-size:.8rem;outline:none;transition:border-color .2s;" @focus="$el.style.borderColor='#6366f1'" @blur="$el.style.borderColor='#d1d5db'"/>
            </div>

            <div>
                <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;display:block;margin-bottom:.25rem;">Identidad/Cédula</label>
                <input wire:model="newClienteIdentidad" type="text" placeholder="Identidad..." style="width:100%;padding:.5rem;border:1.5px solid #d1d5db;border-radius:.5rem;font-size:.8rem;outline:none;transition:border-color .2s;" @focus="$el.style.borderColor='#6366f1'" @blur="$el.style.borderColor='#d1d5db'"/>
            </div>
        </div>

        <div style="display:flex;gap:.65rem;margin-top:1.2rem;">
            <button wire:click="closeNewClienteModal" style="flex:1;padding:.7rem;border-radius:.8rem;border:1.5px solid #e5e7eb;background:transparent;font-weight:700;color:#6b7280;cursor:pointer;font-size:.9rem;transition:all .2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                Cancelar
            </button>
            <button wire:click="saveNewCliente" style="flex:1.5;padding:.7rem;border-radius:.8rem;background:#4f46e5;color:white;font-weight:900;font-size:.95rem;border:none;cursor:pointer;box-shadow:0 3px 10px rgba(79,70,229,.3);transition:all .2s;" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                @if($editingClienteId) Guardar @else Registrar @endif
            </button>
        </div>
    </div>
</div>
@endif

<script>

</script>

</x-filament-panels::page>
