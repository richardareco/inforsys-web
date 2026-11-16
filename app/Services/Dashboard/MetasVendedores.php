<?php

namespace App\Services\Dashboard;

use App\Models\Meta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Avance de las metas del mes para el dashboard administrativo: la meta de toda la empresa y la de cada vendedor que
 * tenga una (metas.personal_id = personal.pernr; vacío = meta de la empresa).
 */
class MetasVendedores
{
    public function __construct(private readonly int $empresaId) {}

    /**
     * @return array{
     *   mes: string, cerrado: bool, esperado: float,
     *   empresa: array{meta: float, vendido: float, pct: float, faltante: float}|null,
     *   vendedores: array<int, array{pernr: string, nombre: string, meta: float, vendido: float, pct: float, faltante: float, estado: string}>,
     *   sin_meta: array<int, string>,
     *   resumen: array{con_meta: int, cumplidas: int, promedio: float}
     * }
     */
    public function delMes(Carbon $mes, ?Carbon $hoy = null): array
    {
        $hoy    = ($hoy ?? today())->copy()->startOfDay();
        $inicio = $mes->copy()->startOfMonth();
        $fin    = $inicio->copy()->endOfMonth()->startOfDay();
        $hasta  = $fin->lt($hoy) ? $fin : $hoy;           // no se cuentan los días que todavía no pasaron
        $cerrado = $fin->lt($hoy);

        $metas = Meta::where('empresa_id', $this->empresaId)->whereDate('mes', $inicio->toDateString())->get();

        $ventas = collect();
        $totalEmpresa = 0.0;
        if ($hasta->gte($inicio)) {
            $filas = DB::connection('delphi')->table('invo1')
                ->where('flag', '4')->whereBetween('fecha', [$inicio->toDateString(), $hasta->toDateString()])
                ->selectRaw('pernr, SUM(total) AS total')->groupBy('pernr')->get();
            $ventas       = $filas->mapWithKeys(fn ($f) => [trim((string) $f->pernr) => (float) $f->total]);
            $totalEmpresa = (float) $filas->sum('total');       // incluye ventas sin vendedor
        }

        $nombres = DB::connection('delphi')->table('personal')->pluck('pername', 'pernr')
            ->mapWithKeys(fn ($n, $p) => [trim((string) $p) => trim((string) $n)]);

        // Cuánto debería llevar a esta altura del mes para llegar justo a la meta
        $esperado = $cerrado ? 100.0 : round($hoy->day / $inicio->daysInMonth * 100, 1);

        $vendedores = [];
        foreach ($metas->where('personal_id', '!=', '') as $m) {
            $pernr   = trim((string) $m->personal_id);
            $meta    = (float) $m->monto;
            if ($meta <= 0) {
                continue;
            }
            $vendido = (float) ($ventas[$pernr] ?? 0);
            $pct     = round($vendido / $meta * 100, 1);

            $vendedores[] = [
                'pernr'    => $pernr,
                'nombre'   => $nombres[$pernr] ?? $pernr,
                'meta'     => $meta,
                'vendido'  => $vendido,
                'pct'      => $pct,
                'faltante' => max(0.0, $meta - $vendido),
                'estado'   => $pct >= 100 ? 'cumplida' : ($pct >= $esperado ? 'al_dia' : 'atrasada'),
            ];
        }
        usort($vendedores, fn ($a, $b) => [$b['pct'], $a['nombre']] <=> [$a['pct'], $b['nombre']]);

        $general = $metas->firstWhere('personal_id', '');
        $empresa = $general && (float) $general->monto > 0 ? [
            'meta'     => (float) $general->monto,
            'vendido'  => $totalEmpresa,
            'pct'      => round($totalEmpresa / (float) $general->monto * 100, 1),
            'faltante' => max(0.0, (float) $general->monto - $totalEmpresa),
        ] : null;

        // Vendedores que vendieron en el mes pero no tienen meta: para que el administrador se las cargue
        $conMeta = array_column($vendedores, 'pernr');
        $sinMeta = $ventas->keys()->filter(fn ($p) => $p !== '' && !in_array($p, $conMeta, true))
            ->map(fn ($p) => $nombres[$p] ?? $p)->sort()->values()->all();

        $n = count($vendedores);

        return [
            'mes'        => ucfirst($inicio->copy()->locale('es')->isoFormat('MMMM YYYY')),
            'cerrado'    => $cerrado,
            'esperado'   => $esperado,
            'empresa'    => $empresa,
            'vendedores' => $vendedores,
            'sin_meta'   => $sinMeta,
            'resumen'    => [
                'con_meta'  => $n,
                'cumplidas' => count(array_filter($vendedores, fn ($v) => $v['pct'] >= 100)),
                'promedio'  => $n > 0 ? round(array_sum(array_column($vendedores, 'pct')) / $n, 1) : 0.0,
            ],
        ];
    }
}
