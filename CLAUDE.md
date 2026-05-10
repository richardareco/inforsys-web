## Detalles Técnicos del Stack (inforsys-web)
- **Framework:** Laravel 12 + PHP 8.2+
- **Panel Admin:** Filament 3 (Ruta principal: `/admin`)
- **Frontend Assets:** Vite 7 + Tailwind CSS 4
- **Herramientas de Desarrollo:** `composer run dev` (gestiona servidor, queue, logs y vite simultáneamente).

## Lógica de Negocio
- **Entidades Core:** Usuario, Empresa, Cliente, Ítem.
- **Dashboard:** Implementado con widgets de Filament (Estadísticas, Gráficos de Ventas, Rankings).
- **Regla de Oro:** Siempre usar los Recursos de Filament (Resources) para los CRUDs y Widgets para las métricas, manteniendo la compatibilidad con las bases de datos externas de los clientes.
