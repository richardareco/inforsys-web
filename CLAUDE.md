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
| Productos / Ítems | Resource | items, itemsdepo, deposito, proveedor, itemsimage, items_ficha (opcional) | ✅ Completo |
| Usuarios | Resource | users (local) | ✅ Completo |
| Empresas | Resource (superadmin) | empresas (local) | ✅ Completo |

### Tablas Delphi creadas para la web
- `items_ficha` (ficha comercial: marca, contenido, descripcion, beneficios, modo_uso, ingredientes, tipo_piel, tonos JSON, advertencias, destacado, mod_fecha; `item` único = `items.item`). La creamos para la web, también se puede completar desde Delphi y **su existencia es opcional por base**: verificar siempre con `FichaProducto::tablaExiste()` y, si falta, caer en `items.descr1`. Es latin1 (el modelo `ItemFicha` limpia lo que no entra en latin1). No se usa `itemsobs` (son observaciones internas de compra). La ficha nunca expone costos.

- `producto_info_extra` (tabla **local** de la web, por empresa): secciones de información extra con título y contenido libres que cada empresa agrega a la ficha de un producto (formato igual a los tonos). Se carga en Productos → pestaña "Información del producto" y se muestra en el modal del POS de catálogo. No toca la base Delphi.

### Dashboard del vendedor
- Un usuario con rol `vendedor` ve en el Dashboard solo sus propias ventas (`invo1.pernr` = su `users.personal_id`): total, ventas, ticket, unidades, ventas por día, por categoría, productos más vendidos y su meta; filtrable por mes o rango. Los widgets están en `app/Filament/Widgets/Vendedor/` y los datos en `Services/Dashboard/VentasVendedor`. Nunca muestra datos de la empresa ni de otros vendedores (ni el PDF del dashboard). Las metas pueden ser de la empresa (`metas.personal_id = ''`) o de un vendedor (`personal_id` = `personal.pernr`).

- Dashboard administrativo: tiene la pestaña **Metas por vendedor** (filtro "General / Metas por vendedor" y selector de mes): meta de la empresa y avance de cada vendedor con meta (servicio `Services/Dashboard/MetasVendedores`; estado Cumplida / Al día / Atrasada según el ritmo esperado del mes). En Metas de Ventas el tipo de meta es "De la empresa" o "Por vendedor".

- Tienda online (módulo `ecommerce`): administradores y superadministradores configuran la tienda; el rol vendedor solo ve la tarjeta del enlace (**Copiar link** y **Abrir tienda**) para enviárselo a un cliente, sin formulario y sin poder guardar (`TiendaOnlinePage::puedeConfigurar()`).

### Caja → Cobros (módulo `cobros`)
- Pantalla del cajero (`CobrosPage`, servicio `Services/Caja/CobrosPendientes`): lista las ventas pendientes de cobro (flag 4, sin `cajanr`, sin crédito) que dejó el POS de empresas con el módulo de caja apagado, y las cobra con efectivo / transferencia / tarjeta (o mezcladas), insertando `cajareg` y enlazando `invo1.cajanr` igual que el POS. El cajero entra directo a esta pantalla. Se mantiene al día consultando cada 2 s una huella liviana (`GET /caja/cobros/firma`, `CobrosFirmaController`) y recargando el listado solo si cambió; avisa con sonido cuando entra una venta nueva. El cobro bloquea la fila de la venta para que dos cajeros no la cobren dos veces.

- Caja → **Consulta de cobros** (`ConsultaCobrosPage`, `Services/Caja/CobrosRealizados`, mismo módulo `cobros`): cobros ya registrados en `cajareg` enlazados a su venta (también los del POS con caja), con resumen por forma de pago, búsqueda, período y botón **Reimprimir** (ticket de la venta con la forma de pago real; si fue mixto lo detalla).

### Tipos de punto de venta (`empresas.tipo`)
- `general` → `pos-page`, `supermercado` → `pos-page-minimal`, `catalogo` → `pos-page-catalogo` (Catálogo / Boutique: grilla con fotos y ficha del producto). Lista en `PosPage::TIPOS`; el administrador de cada empresa lo elige en Configuraciones → Ventas. Cada tipo tiene su propia vista y comparte la lógica de `PosPage`; un cambio en un tipo no debe tocar a los demás.

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
