<?php

namespace App\Services\Dashboard;

use App\Models\Meta;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Datos del dashboard del vendedor: solo las ventas de ese vendedor (invo1.pernr), nunca las de la empresa ni las de
 * otros. Todas las consultas parten de {@see self::ventas()}, que ya trae el filtro por vendedor.
 */
class VentasVendedor
{
    public const PERIODOS = [
        'mes'         => 'Este mes',
        'mes_pasado'  => 'Mes pasado',
        'hoy'         => 'Hoy',
        '7d'          => 'Últimos 7 días',
        '30d'         => 'Últimos 30 días',
        'personalizado' => 'Elegir fechas',
    ];

    private const COLORES = ['#0047AB', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

    private const MAX_DIAS = 366;

    public function __construct(public readonly string $pernr, private readonly ?int $empresaId = null) {}

    /**
     * El vendedor de un usuario con perfil de vendedor. Se busca en `personal` igual que lo hace el POS (comparación
     * flexible entre users.personal_id y personal.pernr). null si no es vendedor o no está vinculado a uno.
     */
    public static function paraUsuario(?User $user): ?self
    {
        if (!$user || !$user->isVendedor() || trim((string) $user->personal_id) === '') {
            return null;
        }

        $pernr = DB::connection('delphi')->table('personal')->pluck('pernr')
            ->first(fn ($p) => (string) $p == (string) $user->personal_id);

        return $pernr === null ? null : new self((string) $pernr, $user->empresa_id ? (int) $user->empresa_id : null);
    }

    // ───────────── Período ─────────────

    /**
     * Rango elegido en el filtro del dashboard.
     *
     * @param  array<string, mixed>  $filtros  periodo, desde, hasta
     * @return array{0: Carbon, 1: Carbon, 2: string}  desde, hasta y una etiqueta legible
     */
    public static function rango(array $filtros, ?Carbon $hoy = null): array
    {
        $hoy = ($hoy ?? today())->copy()->startOfDay();

        [$d, $h] = match ($filtros['periodo'] ?? 'mes') {
            'hoy'        => [$hoy->copy(), $hoy->copy()],
            '7d'         => [$hoy->copy()->subDays(6), $hoy->copy()],
            '30d'        => [$hoy->copy()->subDays(29), $hoy->copy()],
            'mes_pasado' => [$hoy->copy()->subMonthNoOverflow()->startOfMonth(), $hoy->copy()->subMonthNoOverflow()->endOfMonth()->startOfDay()],
            'personalizado' => self::rangoPersonalizado($filtros, $hoy),
            default      => [$hoy->copy()->startOfMonth(), $hoy->copy()->endOfMonth()->startOfDay()],
        };

        return [$d, $h, self::etiqueta($d, $h)];
    }

    private static function rangoPersonalizado(array $f, Carbon $hoy): array
    {
        try {
            $d = filled($f['desde'] ?? null) ? Carbon::parse($f['desde'])->startOfDay() : $hoy->copy()->startOfMonth();
            $h = filled($f['hasta'] ?? null) ? Carbon::parse($f['hasta'])->startOfDay() : $hoy->copy();
        } catch (\Throwable) {
            return [$hoy->copy()->startOfMonth(), $hoy->copy()];
        }

        if ($h->lt($d)) {
            [$d, $h] = [$h, $d];
        }
        if ($d->diffInDays($h) >= self::MAX_DIAS) {
            $d = $h->copy()->subDays(self::MAX_DIAS - 1);
        }

        return [$d, $h];
    }

    public static function etiqueta(Carbon $d, Carbon $h): string
    {
        $d = $d->copy()->locale('es');
        $h = $h->copy()->locale('es');

        if ($d->isSameDay($h)) {
            return $d->isoFormat('D [de] MMMM YYYY');
        }
        if ($d->isSameDay($d->copy()->startOfMonth()) && $h->isSameDay($h->copy()->endOfMonth()) && $d->isSameMonth($h)) {
            return ucfirst($d->isoFormat('MMMM YYYY'));
        }

        return $d->isoFormat('D MMM YYYY') . ' al ' . $h->isoFormat('D MMM YYYY');
    }

    /** El mismo tramo anterior, para comparar: el mes anterior si se mira un mes, o la misma cantidad de días justo antes. */
    public static function anterior(Carbon $d, Carbon $h, ?Carbon $hoy = null): array
    {
        $hoy = ($hoy ?? today())->copy()->startOfDay();
        $h   = $h->lt($hoy) ? $h->copy() : $hoy->copy();                 // los días que todavía no pasaron no cuentan
        $dias = max(1, $d->diffInDays($h) + 1);

        $mesCompleto = $d->isSameDay($d->copy()->startOfMonth());
        $pd = $mesCompleto ? $d->copy()->subMonthNoOverflow()->startOfMonth() : $d->copy()->subDays($dias);

        return [$pd, $pd->copy()->addDays($dias - 1)];
    }

    // ───────────── Consultas ─────────────

    /** Ventas completadas (flag 4) de este vendedor en el rango. */
    private function ventas(Carbon $d, Carbon $h)
    {
        return DB::connection('delphi')->table('invo1')
            ->where('invo1.flag', '4')
            ->where('invo1.pernr', $this->pernr)
            ->whereBetween('invo1.fecha', [$d->toDateString(), $h->toDateString()]);
    }

    /** Renglones vendidos (invo2) de las ventas de este vendedor en el rango. */
    private function renglones(Carbon $d, Carbon $h)
    {
        return $this->ventas($d, $h)
            ->join('invo2', 'invo2.invnr', '=', 'invo1.invnr')
            ->where('invo2.flag', '3');
    }

    /** @return array{total: float, cantidad: int, ticket: float, unidades: float} */
    public function resumen(Carbon $d, Carbon $h): array
    {
        $r = $this->ventas($d, $h)->selectRaw('COUNT(*) AS cantidad, COALESCE(SUM(invo1.total), 0) AS total')->first();
        $cantidad = (int) $r->cantidad;
        $total    = (float) $r->total;

        return [
            'total'    => $total,
            'cantidad' => $cantidad,
            'ticket'   => $cantidad > 0 ? $total / $cantidad : 0.0,
            'unidades' => (float) $this->renglones($d, $h)->sum('invo2.qty'),
        ];
    }

    /**
     * Ventas por día (por mes si el rango es muy largo). Solo hasta hoy: no se dibujan días que todavía no llegaron.
     *
     * @return array{labels: array<int, string>, totales: array<int, float>, cantidades: array<int, int>}
     */
    public function porDia(Carbon $d, Carbon $h, ?Carbon $hoy = null): array
    {
        $hoy = ($hoy ?? today())->copy()->startOfDay();
        $h   = $h->lt($hoy) ? $h->copy() : $hoy;
        if ($h->lt($d)) {
            return ['labels' => [], 'totales' => [], 'cantidades' => []];
        }

        $filas = $this->ventas($d, $h)
            ->selectRaw('invo1.fecha AS dia, COUNT(*) AS cantidad, SUM(invo1.total) AS total')
            ->groupBy('invo1.fecha')->get()->keyBy(fn ($f) => substr((string) $f->dia, 0, 10));

        $porMes = $d->diffInDays($h) > 62;
        $labels = $totales = $cantidades = [];

        for ($dia = $d->copy(); $dia->lte($h); $dia->addDay()) {
            $f = $filas->get($dia->toDateString());
            $clave = $porMes ? $dia->format('Y-m') : $dia->toDateString();
            if (!array_key_exists($clave, $labels)) {
                $labels[$clave] = $porMes ? ucfirst($dia->copy()->locale('es')->isoFormat('MMM YY')) : $dia->copy()->locale('es')->isoFormat('D MMM');
                $totales[$clave] = 0.0;
                $cantidades[$clave] = 0;
            }
            $totales[$clave]    += (float) ($f->total ?? 0);
            $cantidades[$clave] += (int) ($f->cantidad ?? 0);
        }

        return ['labels' => array_values($labels), 'totales' => array_values($totales), 'cantidades' => array_values($cantidades)];
    }

    /** @return array<int, array{nombre: string, monto: float, pct: float, color: string}> */
    public function porCategoria(Carbon $d, Carbon $h): array
    {
        $filas = $this->renglones($d, $h)
            ->leftJoin('items', 'items.item', '=', 'invo2.item')
            ->selectRaw('items.grupnr, (SELECT g.descr FROM grupo g WHERE g.grupnr = items.grupnr LIMIT 1) AS categoria, SUM(invo2.qty * invo2.precio) AS monto')
            ->groupBy('items.grupnr')
            ->orderByDesc('monto')
            ->get();

        // Los productos cuya categoría no existe se juntan en una sola fila "Sin categoría"
        $filas = $filas
            ->groupBy(fn ($f) => trim((string) ($f->categoria ?? '')) !== '' ? trim($f->categoria) : 'Sin categoría')
            ->map(fn ($g, $nombre) => (object) ['categoria' => $nombre, 'monto' => $g->sum(fn ($f) => (float) $f->monto)])
            ->sortByDesc('monto')->values();

        $total = (float) $filas->sum('monto');
        $items = [];
        foreach ($filas->take(6)->values() as $i => $f) {
            $items[] = [
                'nombre' => $f->categoria,
                'monto'  => (float) $f->monto,
                'pct'    => $total > 0 ? (float) $f->monto / $total * 100 : 0.0,
                'color'  => self::COLORES[$i % count(self::COLORES)],
            ];
        }
        $resto = (float) $filas->slice(6)->sum('monto');
        if ($resto > 0) {
            $items[] = ['nombre' => 'Otras categorías', 'monto' => $resto, 'pct' => $total > 0 ? $resto / $total * 100 : 0.0, 'color' => '#94a3b8'];
        }

        return $items;
    }

    /** @return array<int, array{codigo: string, nombre: string, unidades: float, monto: float}> */
    public function topProductos(Carbon $d, Carbon $h, int $n = 8): array
    {
        return $this->renglones($d, $h)
            ->selectRaw('invo2.item AS codigo, MAX(invo2.descr) AS nombre, SUM(invo2.qty) AS unidades, SUM(invo2.qty * invo2.precio) AS monto')
            ->groupBy('invo2.item')
            ->orderByDesc('unidades')->orderByDesc('monto')
            ->limit($n)->get()
            ->map(fn ($f) => [
                'codigo'   => (string) $f->codigo,
                'nombre'   => trim((string) $f->nombre) !== '' ? trim($f->nombre) : (string) $f->codigo,
                'unidades' => (float) $f->unidades,
                'monto'    => (float) $f->monto,
            ])->all();
    }

    /**
     * Meta propia del vendedor para el mes de la fecha dada y cuánto lleva vendido en ese mes. null si no tiene meta.
     *
     * @return array{mes: string, meta: float, vendido: float, pct: float, faltante: float, dias_restantes: int, ritmo: float}|null
     */
    public function meta(Carbon $referencia, ?Carbon $hoy = null): ?array
    {
        if (!$this->empresaId) {
            return null;
        }

        $hoy = ($hoy ?? today())->copy()->startOfDay();
        $mes = $referencia->copy()->startOfMonth();

        $monto = (float) (Meta::where('empresa_id', $this->empresaId)->where('personal_id', $this->pernr)
            ->whereDate('mes', $mes->toDateString())->value('monto') ?? 0);
        if ($monto <= 0) {
            return null;
        }

        $fin     = $mes->copy()->endOfMonth()->startOfDay();
        $hasta   = $fin->lt($hoy) ? $fin : $hoy;
        $vendido = $hasta->lt($mes) ? 0.0 : (float) $this->ventas($mes, $hasta)->sum('invo1.total');
        $faltante = max(0.0, $monto - $vendido);
        $dias     = $hoy->between($mes, $fin) ? (int) $hoy->diffInDays($fin) + 1 : 0;

        return [
            'mes'            => ucfirst($mes->copy()->locale('es')->isoFormat('MMMM YYYY')),
            'meta'           => $monto,
            'vendido'        => $vendido,
            'pct'            => round($vendido / $monto * 100, 1),
            'faltante'       => $faltante,
            'dias_restantes' => $dias,
            'ritmo'          => ($dias > 0 && $faltante > 0) ? $faltante / $dias : 0.0,
        ];
    }
}
