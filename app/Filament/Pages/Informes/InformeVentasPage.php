<?php

namespace App\Filament\Pages\Informes;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InformeVentasPage extends InformeBasePage
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Informes de ventas';
    protected static ?string $title           = 'Informes de ventas';
    protected static ?string $slug            = 'informes/ventas';
    protected static ?int    $navigationSort  = 1;
    protected static string  $acento          = '#16a34a';
    protected static string  $view            = 'filament.pages.informes.ventas';

    public const VISTAS = [
        'periodo'    => 'Por período',
        'vendedores' => 'Vendedores',
        'productos'  => 'Productos',
        'categorias' => 'Categorías',
        'clientes'   => 'Clientes',
        'pagos'      => 'Formas de pago',
        'horario'    => 'Horario',
    ];

    public string $desde = '';
    public string $hasta = '';
    public string $vista = 'periodo';

    public function mount(): void
    {
        $this->desde = now()->startOfMonth()->toDateString();
        $this->hasta = today()->toDateString();
    }

    public function getDescripcion(): string
    {
        return 'Analizá lo que se vendió: por período, vendedor, producto, cliente y forma de pago.';
    }

    public function getContenidos(): array
    {
        return array_values(self::VISTAS);
    }

    public function rango(string $r): void
    {
        [$d, $h] = match ($r) {
            'hoy'    => [today(), today()],
            'ayer'   => [today()->subDay(), today()->subDay()],
            'semana' => [now()->startOfWeek(), today()],
            'año'    => [now()->startOfYear(), today()],
            default  => [now()->startOfMonth(), today()],
        };
        $this->desde = $d->toDateString();
        $this->hasta = $h->toDateString();
    }

    public function setVista(string $v): void
    {
        if (array_key_exists($v, self::VISTAS)) {
            $this->vista = $v;
        }
    }

    public function updatedDesde(): void { $this->normalizarRango(); }
    public function updatedHasta(): void { $this->normalizarRango(); }

    private function normalizarRango(): void
    {
        foreach (['desde', 'hasta'] as $campo) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->$campo)) {
                $this->$campo = today()->toDateString();
            }
        }
        if ($this->desde > $this->hasta) {
            [$this->desde, $this->hasta] = [$this->hasta, $this->desde];
        }
    }

    protected function getViewData(): array
    {
        $this->normalizarRango();

        $desde = Carbon::parse($this->desde);
        $hasta = Carbon::parse($this->hasta);
        $dias  = $desde->diffInDays($hasta) + 1;

        $prevHasta = $desde->copy()->subDay();
        $prevDesde = $prevHasta->copy()->subDays($dias - 1);

        $actual   = $this->resumen($desde, $hasta);
        $anterior = $this->resumen($prevDesde, $prevHasta);

        $kpis = [
            'total'    => $actual['total'],
            'cantidad' => $actual['cantidad'],
            'ticket'   => $actual['cantidad'] > 0 ? $actual['total'] / $actual['cantidad'] : 0,
            'unidades' => $actual['unidades'],
            'cambioTotal'    => $this->cambio($anterior['total'], $actual['total']),
            'cambioCantidad' => $this->cambio($anterior['cantidad'], $actual['cantidad']),
            'cambioTicket'   => $this->cambio(
                $anterior['cantidad'] > 0 ? $anterior['total'] / $anterior['cantidad'] : 0,
                $actual['cantidad'] > 0 ? $actual['total'] / $actual['cantidad'] : 0
            ),
        ];

        $datos = match ($this->vista) {
            'vendedores' => $this->vendedores($desde, $hasta),
            'productos'  => $this->productos($desde, $hasta),
            'categorias' => $this->categorias($desde, $hasta),
            'clientes'   => $this->clientes($desde, $hasta),
            'pagos'      => $this->pagos($desde, $hasta),
            'horario'    => $this->horario($desde, $hasta),
            default      => $this->periodo($desde, $hasta, $dias),
        };

        return [
            'kpis'      => $kpis,
            'datos'     => $datos,
            'dias'      => $dias,
            'rangoTexto' => $desde->equalTo($hasta)
                ? ucfirst($desde->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'))
                : $desde->locale('es')->isoFormat('D MMM YYYY') . ' al ' . $hasta->locale('es')->isoFormat('D MMM YYYY'),
            'hoy'       => today()->toDateString(),
        ];
    }

    private function cambio(float $prev, float $curr): ?float
    {
        return $prev > 0 ? ($curr - $prev) / $prev * 100 : null;
    }

    private function ventas(Carbon $d, Carbon $h)
    {
        return DB::connection('delphi')->table('invo1')
            ->where('invo1.flag', '4')
            ->whereBetween('invo1.fecha', [$d->toDateString(), $h->toDateString()]);
    }

    private function resumen(Carbon $d, Carbon $h): array
    {
        $v = $this->ventas($d, $h)->selectRaw('COUNT(*) AS cantidad, COALESCE(SUM(invo1.total),0) AS total')->first();
        $u = DB::connection('delphi')->table('invo2')
            ->where('flag', '3')
            ->whereBetween('fecha', [$d->toDateString(), $h->toDateString()])
            ->sum('qty');

        return ['cantidad' => (int) $v->cantidad, 'total' => (float) $v->total, 'unidades' => (float) $u];
    }

    private function periodo(Carbon $d, Carbon $h, int $dias): array
    {
        $porMes = $dias > 62;
        $expr   = $porMes ? "DATE_FORMAT(invo1.fecha, '%Y-%m')" : 'invo1.fecha';

        $filas = $this->ventas($d, $h)
            ->selectRaw("$expr AS k, COUNT(*) AS cant, COALESCE(SUM(invo1.total),0) AS total")
            ->groupBy('k')->orderBy('k')->get()->keyBy('k');

        $serie = [];
        if ($porMes) {
            $cursor = $d->copy()->startOfMonth();
            while ($cursor->lte($h)) {
                $k = $cursor->format('Y-m');
                $serie[] = [
                    'etiqueta' => ucfirst($cursor->locale('es')->isoFormat('MMM YYYY')),
                    'corta'    => ucfirst($cursor->locale('es')->isoFormat('MMM')),
                    'cant'     => (int) ($filas[$k]->cant ?? 0),
                    'total'    => (float) ($filas[$k]->total ?? 0),
                ];
                $cursor->addMonth();
            }
        } else {
            $cursor = $d->copy();
            while ($cursor->lte($h)) {
                $k = $cursor->toDateString();
                $serie[] = [
                    'etiqueta' => ucfirst($cursor->locale('es')->isoFormat('ddd D/M')),
                    'corta'    => $cursor->format('j'),
                    'cant'     => (int) ($filas[$k]->cant ?? 0),
                    'total'    => (float) ($filas[$k]->total ?? 0),
                ];
                $cursor->addDay();
            }
        }

        $total = array_sum(array_column($serie, 'total'));
        $max   = max(array_column($serie, 'total') ?: [0]);
        $mejor = collect($serie)->sortByDesc('total')->first();

        return [
            'serie' => $serie, 'total' => $total, 'max' => $max,
            'porMes' => $porMes,
            'mejor' => ($mejor && $mejor['total'] > 0) ? $mejor : null,
        ];
    }

    private function vendedores(Carbon $d, Carbon $h): array
    {
        $filas = $this->ventas($d, $h)
            ->leftJoin('personal', 'personal.pernr', '=', 'invo1.pernr')
            ->selectRaw("COALESCE(MAX(personal.pername), 'Sin vendedor') AS nombre, COALESCE(MAX(personal.porc_comision), 0) AS comision, COUNT(*) AS cant, COALESCE(SUM(invo1.total),0) AS total")
            ->groupBy('invo1.pernr')->orderByDesc('total')->get();

        $total = (float) $filas->sum('total');

        return ['filas' => $filas, 'total' => $total, 'max' => (float) ($filas->max('total') ?? 0)];
    }

    private function productos(Carbon $d, Carbon $h): array
    {
        $filas = DB::connection('delphi')->table('invo2')
            ->where('flag', '3')
            ->whereBetween('fecha', [$d->toDateString(), $h->toDateString()])
            ->selectRaw('item, MAX(descr) AS descr, SUM(qty) AS qty, COUNT(DISTINCT invnr) AS ventas, SUM(qty * precio) AS ingresos')
            ->groupBy('item')->orderByDesc('ingresos')->limit(30)->get();

        $total = (float) DB::connection('delphi')->table('invo2')
            ->where('flag', '3')
            ->whereBetween('fecha', [$d->toDateString(), $h->toDateString()])
            ->sum(DB::raw('qty * precio'));

        return ['filas' => $filas, 'total' => $total, 'max' => (float) ($filas->max('ingresos') ?? 0)];
    }

    private function categorias(Carbon $d, Carbon $h): array
    {
        $filas = DB::connection('delphi')->table('invo2')
            ->join('items', 'items.item', '=', 'invo2.item')
            ->where('invo2.flag', '3')
            ->whereBetween('invo2.fecha', [$d->toDateString(), $h->toDateString()])
            ->selectRaw('items.grupnr, (SELECT g.descr FROM grupo g WHERE g.grupnr = items.grupnr LIMIT 1) AS categoria, SUM(invo2.qty) AS qty, SUM(invo2.qty * invo2.precio) AS ingresos')
            ->groupBy('items.grupnr')->orderByDesc('ingresos')->get();

        $total = (float) $filas->sum('ingresos');

        return ['filas' => $filas, 'total' => $total, 'max' => (float) ($filas->max('ingresos') ?? 0)];
    }

    private function clientes(Carbon $d, Carbon $h): array
    {
        $filas = $this->ventas($d, $h)
            ->join('cliente', 'cliente.custnr', '=', 'invo1.custnr')
            ->selectRaw('cliente.custnr, MAX(cliente.custname) AS nombre, COUNT(*) AS cant, SUM(invo1.total) AS total, MAX(invo1.fecha) AS ultima')
            ->groupBy('cliente.custnr')->orderByDesc('total')->limit(30)->get();

        $total = (float) $this->ventas($d, $h)->sum('invo1.total');

        return ['filas' => $filas, 'total' => $total, 'max' => (float) ($filas->max('total') ?? 0)];
    }

    private function pagos(Carbon $d, Carbon $h): array
    {
        $c = DB::connection('delphi')->table('cajareg')
            ->whereBetween('fecha', [$d->toDateString(), $h->toDateString()])
            ->where('flag', '!=', 'c')
            ->where(fn ($q) => $q->whereNull('es_devolucion')->orWhere('es_devolucion', 0))
            ->where(fn ($q) => $q->whereNull('salida_tesoreria')->orWhere('salida_tesoreria', 0))
            ->selectRaw('
                COALESCE(SUM(paygs),0) AS ef, COALESCE(SUM(paytr),0) AS tr, COALESCE(SUM(paytj),0) AS tj,
                COALESCE(SUM(paycq),0) AS cq, COALESCE(SUM(payot),0) AS ot,
                COALESCE(SUM(paygs > 0),0) AS n_ef, COALESCE(SUM(paytr > 0),0) AS n_tr, COALESCE(SUM(paytj > 0),0) AS n_tj,
                COALESCE(SUM(paycq > 0),0) AS n_cq, COALESCE(SUM(payot > 0),0) AS n_ot
            ')->first();

        $cr = $this->ventas($d, $h)->where('invo1.acredito', '>', 0)
            ->selectRaw('COUNT(*) AS n, COALESCE(SUM(invo1.total),0) AS total')->first();

        $metodos = [
            ['label' => 'Efectivo',      'monto' => (float) $c->ef, 'n' => (int) $c->n_ef, 'color' => '#16a34a'],
            ['label' => 'Transferencia', 'monto' => (float) $c->tr, 'n' => (int) $c->n_tr, 'color' => '#2563eb'],
            ['label' => 'Tarjeta',       'monto' => (float) $c->tj, 'n' => (int) $c->n_tj, 'color' => '#9333ea'],
            ['label' => 'Cheque',        'monto' => (float) $c->cq, 'n' => (int) $c->n_cq, 'color' => '#d97706'],
            ['label' => 'Otros',         'monto' => (float) $c->ot, 'n' => (int) $c->n_ot, 'color' => '#64748b'],
            ['label' => 'Crédito',       'monto' => (float) $cr->total, 'n' => (int) $cr->n, 'color' => '#ef4444'],
        ];
        $metodos = array_values(array_filter($metodos, fn ($m) => $m['monto'] > 0 || in_array($m['label'], ['Efectivo', 'Transferencia', 'Tarjeta'], true)));
        $total = array_sum(array_column($metodos, 'monto'));

        return ['metodos' => $metodos, 'total' => $total];
    }

    private function horario(Carbon $d, Carbon $h): array
    {
        $porHora = $this->ventas($d, $h)->whereNotNull('invo1.hora')
            ->selectRaw('CAST(SUBSTRING(invo1.hora, 1, 2) AS UNSIGNED) AS h, COUNT(*) AS cant, SUM(invo1.total) AS total')
            ->groupBy('h')->get()->keyBy('h');

        $horas = [];
        for ($i = 0; $i < 24; $i++) {
            $horas[] = ['h' => $i, 'cant' => (int) ($porHora[$i]->cant ?? 0), 'total' => (float) ($porHora[$i]->total ?? 0)];
        }
        // Se muestran solo las horas con actividad en el negocio (primera a última)
        $activas = array_keys(array_filter($horas, fn ($x) => $x['cant'] > 0));
        $horas = $activas ? array_slice($horas, min($activas), max($activas) - min($activas) + 1) : [];

        $porDia = $this->ventas($d, $h)
            ->selectRaw('DAYOFWEEK(invo1.fecha) AS d, COUNT(*) AS cant, SUM(invo1.total) AS total')
            ->groupBy('d')->get()->keyBy('d');

        $nombres = [2 => 'Lunes', 3 => 'Martes', 4 => 'Miércoles', 5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado', 1 => 'Domingo'];
        $dias = [];
        foreach ($nombres as $k => $n) {
            $dias[] = ['nombre' => $n, 'cant' => (int) ($porDia[$k]->cant ?? 0), 'total' => (float) ($porDia[$k]->total ?? 0)];
        }

        return [
            'horas' => $horas, 'maxHora' => max(array_column($horas, 'total') ?: [0]),
            'dias'  => $dias,  'maxDia'  => max(array_column($dias, 'total') ?: [0]),
        ];
    }
}
