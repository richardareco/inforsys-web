<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class PresupuestoCrearPage extends Page
{
    protected static string  $view           = 'filament.pages.presupuesto-crear';
    protected static bool    $shouldRegisterNavigation = false;
    protected static ?string $title          = 'Nuevo Presupuesto';
    protected static ?string $slug           = 'presupuesto/crear';

    public string $search         = '';
    public array  $searchResults  = [];
    public array  $cart           = [];
    public mixed  $clienteId      = null;
    public mixed  $depositoId     = null;
    public string $obs            = '';
    public string $clienteCelular = '';

    public array $clientes  = [];
    public array $depositos = [];

    public function mount(): void
    {
        // Solo custnr + custname para el combobox — evita caracteres inválidos de otros campos
        $this->clientes = DB::connection('delphi')
            ->table('cliente')
            ->select('custnr', 'custname')
            ->orderBy('custnr')
            ->limit(1000)
            ->get()
            ->map(fn ($c) => (object)[
                'custnr'   => $c->custnr,
                'custname' => mb_convert_encoding((string) ($c->custname ?? ''), 'UTF-8', 'UTF-8'),
            ])
            ->toArray();

        $this->depositos = DB::connection('delphi')
            ->table('deposito')->select('deponr', 'depo_nombre')
            ->orderBy('deponr')->get()->toArray();

        if ($this->clientes) {
            $this->clienteId      = $this->clientes[0]->custnr;
            $this->clienteCelular = $this->fetchCelular($this->clienteId);
        }
        if ($this->depositos) $this->depositoId = $this->depositos[0]->deponr;
    }

    public function updatedClienteId(): void
    {
        $this->clienteCelular = $this->fetchCelular($this->clienteId);
    }

    private function fetchCelular(mixed $custnr): string
    {
        return (string) (DB::connection('delphi')
            ->table('cliente')
            ->where('custnr', $custnr)
            ->value('celular1') ?? '');
    }

    public function updatedSearch(): void
    {
        $term = trim($this->search);
        if (strlen($term) < 2) { $this->searchResults = []; return; }

        $like    = '%' . $term . '%';
        $results = DB::connection('delphi')->table('items')
            ->select('item', 'scode', 'descr', 'precio3 as precio', 'costo')
            ->where(fn ($q) => $q
                ->where('descr',  'like', $like)
                ->orWhere('scode', 'like', $like)
                ->orWhere('item',  'like', $like))
            ->orderBy('descr')->limit(12)->get()->toArray();

        // Coincidencia exacta por código → seleccionar automáticamente
        $exact = collect($results)->first(fn ($r) =>
            strtolower((string)($r->item  ?? '')) === strtolower($term) ||
            strtolower((string)($r->scode ?? '')) === strtolower($term)
        );

        if ($exact) {
            $this->search        = '';
            $this->searchResults = [];
            $this->dispatch('item-exacto', item: (array) $exact);
            return;
        }

        $this->searchResults = $results;
    }

    // Agrega ítem al carrito con qty y precio ya definidos desde el formulario
    public function addItemWithParams(string $itemCode, string $descr, float $qty, float $precio, float $costo): void
    {
        $idx = collect($this->cart)->search(fn ($c) => $c['item'] === $itemCode);
        if ($idx !== false) {
            $this->cart[$idx]['qty']    += max(1, $qty);
            $this->cart[$idx]['precio'] = max(0, $precio);
        } else {
            $this->cart[] = [
                'item'   => $itemCode,
                'descr'  => $descr,
                'qty'    => max(1, $qty),
                'precio' => max(0, $precio),
                'costo'  => $costo,
            ];
        }
        $this->search        = '';
        $this->searchResults = [];
        $this->dispatch('focus-search');
    }

    public function updateCartItem(int $index, float $qty, float $precio): void
    {
        if (!isset($this->cart[$index])) return;
        $this->cart[$index]['qty']    = max(1, $qty);
        $this->cart[$index]['precio'] = max(0, $precio);
    }

    public function removeFromCart(int $index): void
    {
        array_splice($this->cart, $index, 1);
        $this->cart = array_values($this->cart);
        $this->dispatch('focus-search');
    }

    public function getTotal(): float
    {
        return (float) collect($this->cart)->sum(fn ($i) => $i['qty'] * $i['precio']);
    }

    public function guardar(): void
    {
        $presnr = $this->procesarGuardado();
        if ($presnr === null) return;

        $this->dispatch('abrir-pdf', url: route('presupuesto.pdf', $presnr));
        $this->resetFormulario();
    }

    public function guardarWhatsapp(): void
    {
        $total  = $this->getTotal();
        $presnr = $this->procesarGuardado();
        if ($presnr === null) return;

        $phone   = preg_replace('/\D/', '', $this->clienteCelular);
        $cliente = collect($this->clientes)->first(fn ($c) => $c->custnr == $this->clienteId);
        $nombre  = $cliente?->custname ?? '';
        $mensaje = "Hola {$nombre}, le enviamos el presupuesto Nro. {$presnr} por Gs. "
            . number_format($total, 0, ',', '.')
            . ". Puede verlo en: " . route('presupuesto.pdf', $presnr);

        $this->dispatch('abrir-whatsapp', url: 'https://wa.me/' . $phone . '?text=' . urlencode($mensaje));
        $this->resetFormulario();
    }

    private function procesarGuardado(): ?string
    {
        if (empty($this->cart)) {
            Notification::make()->title('Agregue al menos un ítem')->warning()->send();
            return null;
        }

        if (!$this->clienteId) {
            Notification::make()->title('Seleccione un cliente')->warning()->send();
            return null;
        }

        try {
            $total  = $this->getTotal();
            $ctotal = (float) collect($this->cart)->sum(fn ($i) => $i['qty'] * $i['costo']);
            $hoy    = today()->toDateString();
            $hora   = now()->format('H:i:s');

            DB::connection('delphi')->table('presupuesto1')->insert([
                'custnr'      => $this->clienteId,
                'depo'        => $this->depositoId,
                'obs'         => $this->obs ?: null,
                'total'       => $total,
                'totalv'      => $total,
                'ctotal'      => $ctotal,
                'dscto'       => 0,
                'pdscto'      => 0,
                'fecha'       => $hoy,
                'hora'        => $hora,
                'anulado'     => 0,
                'cerrado'     => 0,
                'enviado'     => 0,
                'tipo_moneda' => 'GS',
            ]);

            $nro    = DB::connection('delphi')->getPdo()->lastInsertId();
            $presnr = DB::connection('delphi')->table('presupuesto1')->where('nro', $nro)->value('presnr');

            foreach ($this->cart as $item) {
                DB::connection('delphi')->table('presupuesto2')->insert([
                    'presnr'  => $presnr,
                    'item'    => $item['item'],
                    'descr'   => $item['descr'],
                    'qty'     => $item['qty'],
                    'precio'  => $item['precio'],
                    'costo'   => $item['costo'],
                    'fecha'   => $hoy,
                    'anulado' => 0,
                    'custnr'  => $this->clienteId,
                ]);
            }

            Notification::make()->title('Presupuesto ' . $presnr . ' guardado')->success()->send();

            return $presnr;

        } catch (\Throwable $e) {
            Notification::make()->title('Error al guardar')->body($e->getMessage())->danger()->send();
            return null;
        }
    }

    private function resetFormulario(): void
    {
        $this->cart   = [];
        $this->obs    = '';
        $this->search = '';
    }
}
