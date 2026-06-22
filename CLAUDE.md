## Detalles Técnicos del Stack (inforsys-web)
- **Framework:** Laravel 12 + PHP 8.2+
- **Panel Admin:** Filament 3 (Ruta principal: `/admin`)
- **Frontend Assets:** Vite 7 + Tailwind CSS 4
- **Herramientas de Desarrollo:** `composer run dev` (gestiona servidor, queue, logs y vite simultáneamente).

## Lógica de Negocio
- **Entidades Core:** Usuario, Empresa, Cliente, Ítem.
- **Dashboard:** Implementado con widgets de Filament (Estadísticas, Gráficos de Ventas, Rankings).
- **Regla de Oro:** Siempre usar los Recursos de Filament (Resources) para los CRUDs y Widgets para las métricas, manteniendo la compatibilidad con las bases de datos externas de los clientes.

## Módulos Existentes
| Módulo | Tipo | Tabla Delphi | Estado |
|--------|------|--------------|--------|
| POS / Punto de Venta | Filament Page (Livewire) | invo1, invo2, cajareg, items, itemsdepo | ✅ Completo |
| Dashboard Analytics | Widgets Filament | invo1, gastos, dev_cliente1, cuentas_acobrar | ✅ Completo |
| Clientes | Resource | cliente | ✅ Completo |
| Productos / Ítems | Resource (solo lectura) | items, itemsdepo, deposito, proveedor | ✅ Completo |
| Usuarios | Resource | users (local) | ✅ Completo |
| Empresas | Resource (superadmin) | empresas (local) | ✅ Completo |

## Roadmap de Módulos (prioridad según impacto para clientes)

### Alta Prioridad
- **Historial de Ventas / Facturas** — Listado de `invo1` con filtros, detalle de ítems (`invo2`), reimprimir comprobante. Complemento natural del POS.
- **Cierre de Caja** — Resumen diario de `cajareg` (efectivo, transferencia, tarjeta), comparación vs ventas, botón cerrar caja.
- **Cuentas por Cobrar** — Tabla `cuentas_acobrar`, deudas por cliente, vencimientos, marcar pagos.

### Media Prioridad
- **Proveedores** — CRUD o lectura de tabla `proveedor` (ya existe en Delphi).
- **Compras** — Historial de `compra1`, similar al módulo de ventas.
- **Gastos** — Registro de `gastos` directamente desde la app.

### Utilidades
- **Reportes PDF / Excel** — Ventas por período/vendedor/producto. Exportar con logo de la empresa (`barryvdh/laravel-dompdf` + `filament-excel`).
- **Personal / Vendedores** — Gestión de tabla `personal` (ya usada en widget de vendedores).

## Dashboard — Mejoras Planificadas
- **Exportar a PDF** con logo de la empresa del usuario autenticado (usando `barryvdh/laravel-dompdf`)
- **Gráfico adicional:** comparativo por categoría de producto o por método de pago (efectivo vs tarjeta vs transferencia)
- Las tarjetas de estadísticas son correctas, mantener el diseño actual
- PDF debe incluir: logo empresa, nombre empresa, período seleccionado, todas las tarjetas KPI, todos los gráficos, fecha de generación
