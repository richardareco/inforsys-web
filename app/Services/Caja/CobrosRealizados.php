<?php

namespace App\Services\Caja;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Consulta de cobros: los movimientos de caja ya registrados (cajareg) enlazados a su venta, para que el cajero los
 * revise y los reimprima. Incluye los cobros hechos desde Caja → Cobros y los del POS con módulo de caja.
 */
class CobrosRealizados
{
    public const PERIODOS = ['hoy' => 'Hoy', 'ayer' => 'Ayer', '7d' => 'Últimos 7 días', 'mes' => 'Este mes', '30d' => 'Últimos 30 días'];

    public const FORMAS = ['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'tarjeta' => 'Tarjeta'];

    private const LIMITE = 100;

    private function db()
    {
        return DB::connection('delphi');
    }

    /** @return array{0: Carbon, 1: Carbon} */
    public static function rango(string $periodo, ?Carbon $ahora = null): array
    {
        $hoy = ($ahora ?? now())->copy()->startOfDay();

        return match ($periodo) {
            'ayer'  => [$hoy->copy()->subDay(), $hoy->copy()->subDay()],
            '7d'    => [$hoy->copy()->subDays(6), $hoy],
            '30d'   => [$hoy->copy()->subDays(29), $hoy],
            'mes'   => [$hoy->copy()->startOfMonth(), $hoy],
            default => [$hoy, $hoy],
        };
    }

    /** Cobros de ventas completadas (no devoluciones) en el rango. */
    private function consulta(Carbon $desde, Carbon $hasta, string $buscar, string $forma)
    {
        $q = $this->db()->table('cajareg')
            ->join('invo1', 'invo1.cajanr', '=', 'cajareg.cajanr')
            ->leftJoin('cliente', 'cliente.custnr', '=', 'invo1.custnr')
            ->leftJoin('personal', 'personal.pernr', '=', 'invo1.pernr')
            ->where('invo1.flag', '4')
            ->where('cajareg.op', 'PC')
            ->where(fn ($w) => $w->whereNull('cajareg.es_devolucion')->orWhere('cajareg.es_devolucion', 0))
            ->whereBetween('cajareg.fecha', [$desde->toDateString(), $hasta->toDateString()]);

        $buscar = trim($buscar);
        if ($buscar !== '') {
            $like = '%' . addcslashes($buscar, '%_\\') . '%';
            $q->where(fn ($w) => $w->where('cajareg.cajanr', 'like', $like)->orWhere('invo1.invnr', 'like', $like)
                ->orWhere('cliente.custname', 'like', $like)->orWhere('personal.pername', 'like', $like));
        }

        $columna = ['efectivo' => 'cajareg.paygs', 'transferencia' => 'cajareg.paytr', 'tarjeta' => 'cajareg.paytj'][$forma] ?? null;
        if ($columna) {
            $q->where($columna, '>', 0);
        }

        return $q;
    }

    /**
     * @return array{cobros: array<int, array<string, mixed>>, total: float, cantidad: int, por_forma: array<string, float>, hay_mas: bool}
     */
    public function listar(string $periodo = 'hoy', string $buscar = '', string $forma = '', ?Carbon $ahora = null): array
    {
        [$desde, $hasta] = self::rango($periodo, $ahora);

        $resumen = $this->consulta($desde, $hasta, $buscar, $forma)
            ->selectRaw('COUNT(*) AS n, COALESCE(SUM(cajareg.total), 0) AS total, COALESCE(SUM(cajareg.paygs), 0) AS efectivo, COALESCE(SUM(cajareg.paytr), 0) AS transferencia, COALESCE(SUM(cajareg.paytj), 0) AS tarjeta')
            ->first();

        $filas = $this->consulta($desde, $hasta, $buscar, $forma)
            ->selectRaw('cajareg.nro, cajareg.cajanr, cajareg.fecha, cajareg.total, cajareg.paygs, cajareg.paytr, cajareg.paytj, cajareg.paycq, cajareg.payot,
                invo1.invnr, invo1.hora, cliente.custname AS cliente, personal.pername AS vendedor')
            ->orderByDesc('cajareg.nro')->limit(self::LIMITE)->get();

        $cobros = $filas->map(function ($f) {
            $pagos = array_filter([
                'efectivo'      => (float) $f->paygs,
                'transferencia' => (float) $f->paytr,
                'tarjeta'       => (float) $f->paytj,
                'otros'         => (float) $f->paycq + (float) $f->payot,
            ], fn ($v) => $v > 0);

            return [
                'cajanr'   => (string) $f->cajanr,
                'invnr'    => (string) $f->invnr,
                'numero'   => str_pad((string) $f->invnr, 6, '0', STR_PAD_LEFT),
                'fecha'    => Carbon::parse(substr((string) $f->fecha, 0, 10))->locale('es')->isoFormat('ddd D MMM'),
                'hora'     => $f->hora ? substr((string) $f->hora, 0, 5) : '',
                'total'    => (float) $f->total,
                'pagos'    => $pagos,
                'cliente'  => trim((string) $f->cliente) !== '' ? trim(mb_convert_encoding((string) $f->cliente, 'UTF-8', 'UTF-8')) : 'Cliente ocasional',
                'vendedor' => trim((string) $f->vendedor) !== '' ? trim(mb_convert_encoding((string) $f->vendedor, 'UTF-8', 'UTF-8')) : '—',
                // Para reimprimir: la forma principal y, si fue una sola, el importe
                'medio'    => $pagos ? (string) array_key_first(collect($pagos)->sortDesc()->all()) : 'efectivo',
            ];
        })->all();

        return [
            'cobros'    => $cobros,
            'total'     => (float) $resumen->total,
            'cantidad'  => (int) $resumen->n,
            'por_forma' => ['efectivo' => (float) $resumen->efectivo, 'transferencia' => (float) $resumen->transferencia, 'tarjeta' => (float) $resumen->tarjeta],
            'hay_mas'   => (int) $resumen->n > self::LIMITE,
        ];
    }
}
