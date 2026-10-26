<?php

namespace App\Support;

use App\Models\User;

/**
 * Registro único de módulos del sistema. Cada módulo es una casilla en Usuarios → Accesos y
 * la misma clave controla el menú, la URL directa y las acciones de ese módulo.
 * Empresas y Usuarios son solo del superadministrador (no se asignan por acceso).
 */
class Modulos
{
    /** clave => [etiqueta, grupo, descripción] */
    public const LISTA = [
        'dashboard'        => ['Dashboard', 'Principal', 'Resumen de ventas, gráficos y ranking'],
        'ventas'           => ['Ventas (POS)', 'Ventas', 'Punto de venta'],
        'historial_ventas' => ['Ventas realizadas', 'Ventas', 'Consultar e imprimir ventas hechas'],
        'presupuestos'     => ['Presupuestos', 'Ventas', 'Crear y consultar presupuestos'],
        'whatsapp'         => ['WhatsApp: conversaciones, pedidos y cotizaciones', 'Ventas', 'Atender chats y gestionar pedidos del agente'],
        'ecommerce'        => ['Tienda online (catálogo web)', 'E-commerce', 'Configurar el catálogo público y qué datos se muestran'],
        'clientes'         => ['Clientes', 'Catálogo', 'Alta y consulta de clientes'],
        'productos'        => ['Productos / Ítems', 'Catálogo', 'Consulta y edición de productos'],
        'cierre_caja'      => ['Cierre de caja', 'Caja', 'Resumen y cierre diario de caja'],
        'reportes'         => ['Informes', 'Informes', 'Informes de ventas, compras, ganancias, stock, caja y financieros'],
        'metas'            => ['Metas de ventas', 'Configuración', 'Definir metas por vendedor'],
        'agente_whatsapp'  => ['Configurar el agente de WhatsApp', 'Configuración', 'Datos del negocio, mayorista y modo del agente'],
        'configuraciones'  => ['Configuraciones del sistema', 'Administración', 'Caja, stock, decimales y otras opciones'],
    ];

    public static function etiquetas(): array
    {
        return array_map(fn ($m) => $m[0], self::LISTA);
    }

    /** @return array<string, array<string, array{0: string, 1: string, 2: string}>> módulos agrupados */
    public static function porGrupo(): array
    {
        $grupos = [];
        foreach (self::LISTA as $clave => $m) {
            $grupos[$m[1]][$clave] = $m;
        }

        return $grupos;
    }

    /** Accesos sugeridos al crear un usuario según su rol. */
    public static function sugeridos(?string $rol): array
    {
        return match ($rol) {
            'administrador' => array_keys(self::LISTA),
            'cajero'        => ['ventas', 'clientes', 'productos', 'cierre_caja'],
            default         => ['ventas', 'clientes', 'productos'],
        };
    }

    public static function permite(?User $user, string $modulo): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin() || (bool) (($user->accesos ?? [])[$modulo] ?? false);
    }

    /**
     * Primera pantalla que le corresponde al usuario al entrar al sistema:
     * vendedores y cajeros van al POS; el resto al dashboard, o al primer módulo permitido.
     */
    public static function inicio(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        $candidatos = [];
        if (in_array($user->role, ['vendedor', 'cajero'], true)) {
            $candidatos[] = ['ventas', \App\Filament\Pages\PosPage::class];
        }

        $candidatos = array_merge($candidatos, [
            ['dashboard', \App\Filament\Pages\Dashboard::class],
            ['ventas', \App\Filament\Pages\PosPage::class],
            ['historial_ventas', \App\Filament\Resources\HistorialVentasResource::class],
            ['presupuestos', \App\Filament\Resources\PresupuestoResource::class],
            ['whatsapp', \App\Filament\Pages\ConversacionesWhatsAppPage::class],
            ['clientes', \App\Filament\Resources\ClienteResource::class],
            ['productos', \App\Filament\Resources\ItemResource::class],
            ['cierre_caja', \App\Filament\Pages\CierreCajaPage::class],
            ['reportes', \App\Filament\Pages\Informes\InformeVentasPage::class],
            ['metas', \App\Filament\Resources\MetaResource::class],
            ['ecommerce', \App\Filament\Pages\TiendaOnlinePage::class],
            ['agente_whatsapp', \App\Filament\Pages\AgenteWhatsAppPage::class],
            ['configuraciones', \App\Filament\Pages\ConfiguracionesVentasPage::class],
        ]);

        foreach ($candidatos as [$modulo, $clase]) {
            if (self::permite($user, $modulo)) {
                return $clase::getUrl();
            }
        }

        return null;
    }
}
