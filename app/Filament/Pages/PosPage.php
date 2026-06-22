<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class PosPage extends Page
{
    protected static string  $view            = 'filament.pages.pos-page';
    protected static ?string $navigationIcon  = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'POS';
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?string $title           = 'Punto de Venta';
    protected static ?int    $navigationSort  = 1;

    // Búsqueda
    public string $search        = '';
    public array  $searchResults = [];

    // Selección de contexto
    public mixed  $clienteId  = null;
    public mixed  $depositoId = null;
    public mixed  $cajeroId   = null;

    // Carrito
    public array $cart = [];

    // Modal de pago
    public bool   $showPaymentModal = false;
    public string $paymentMethod    = 'efectivo';
    public float  $amountReceived   = 0;

    // Combos para los selects
    public array $clientes  = [];
    public array $depositos = [];
    public array $cajeros   = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('fullscreen')
                ->label('Pantalla completa')
                ->icon('heroicon-o-arrows-pointing-out')
                ->color('gray')
                ->outlined()
                ->action(fn () => $this->dispatch('toggle-fullscreen')),
        ];
    }

    public function mount(): void
    {
        $this->clientes = DB::connection('delphi')
            ->table('cliente')
            ->select('custnr', 'custname')
            ->orderBy('custnr')
            ->limit(300)
            ->get()
            ->toArray();

        $this->depositos = DB::connection('delphi')
            ->table('deposito')
            ->select('deponr', 'depo_nombre')
            ->orderBy('deponr')
            ->get()
            ->toArray();

        $this->cajeros = DB::connection('delphi')
            ->table('personal')
            ->select('pernr', 'pername')
            ->orderBy('pernr')
            ->get()
            ->toArray();

        if ($this->clientes)  $this->clienteId  = $this->clientes[0]->custnr;
        if ($this->depositos) $this->depositoId = $this->depositos[0]->deponr;
        if ($this->cajeros)   $this->cajeroId   = $this->cajeros[0]->pernr;
    }

    public function updatedSearch(): void
    {
        $term = trim($this->search);

        if (strlen($term) < 2) {
            $this->searchResults = [];
            return;
        }

        $like = '%' . $term . '%';

        $results = DB::connection('delphi')
            ->table('items')
            ->select('items.item', 'items.scode', 'items.scode1', 'items.descr', 'items.precio3 as precio', 'items.costo')
            ->selectRaw(
                'COALESCE((SELECT qty FROM itemsdepo WHERE itemsdepo.item = items.item AND itemsdepo.depo = ?), 0) AS stock_depo',
                [$this->depositoId]
            )
            ->where(function ($q) use ($like) {
                $q->where('items.descr',  'like', $like)
                  ->orWhere('items.scode',  'like', $like)
                  ->orWhere('items.scode1', 'like', $like)
                  ->orWhere('items.item',   'like', $like);
            })
            ->where('items.stock', '>', 0)
            ->orderBy('items.descr')
            ->limit(12)
            ->get()
            ->toArray();

        // Si el término coincide exactamente con item, scode o scode1 → agregar al carrito automáticamente
        $exact = collect($results)->first(fn ($r) =>
            strtolower((string) ($r->item   ?? '')) === strtolower($term) ||
            strtolower((string) ($r->scode  ?? '')) === strtolower($term) ||
            strtolower((string) ($r->scode1 ?? '')) === strtolower($term)
        );

        if ($exact) {
            $this->addToCartItem($exact);
            return;
        }

        $this->searchResults = $results;
    }

    private function addToCartItem(object $item): void
    {
        $idx = collect($this->cart)->search(fn ($c) => $c['item'] === $item->item);

        if ($idx !== false) {
            $this->cart[$idx]['qty']++;
        } else {
            $this->cart[] = [
                'item'       => $item->item,
                'descr'      => $item->descr,
                'qty'        => 1,
                'precio'     => (float) $item->precio,
                'costo'      => (float) $item->costo,
                'stock_depo' => (int)   $item->stock_depo,
            ];
        }

        $this->search        = '';
        $this->searchResults = [];
        $this->dispatch('focus-search');
    }

    public function updatedDepositoId(): void
    {
        $this->search        = '';
        $this->searchResults = [];
        $this->cart          = [];
    }

    public function addToCart(string $itemCode): void
    {
        $item = collect($this->searchResults)->first(fn($r) => $r->item === $itemCode);
        if (!$item) return;

        $idx = collect($this->cart)->search(fn($c) => $c['item'] === $itemCode);

        if ($idx !== false) {
            $this->cart[$idx]['qty']++;
        } else {
            $this->cart[] = [
                'item'       => $item->item,
                'descr'      => $item->descr,
                'qty'        => 1,
                'precio'     => (float) $item->precio,
                'costo'      => (float) $item->costo,
                'stock_depo' => (int)   $item->stock_depo,
            ];
        }

        $this->search        = '';
        $this->searchResults = [];
        $this->dispatch('focus-search');
    }

    public function updateQty(int $index, mixed $qty): void
    {
        if (!isset($this->cart[$index])) return;
        $this->cart[$index]['qty'] = max(1, (int) $qty);
    }

    public function updateCartItem(int $index, int $qty, float $precio): void
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

    public function getCartTotal(): float
    {
        return (float) collect($this->cart)->sum(fn($i) => $i['precio'] * $i['qty']);
    }

    public function getCartCosto(): float
    {
        return (float) collect($this->cart)->sum(fn($i) => $i['costo'] * $i['qty']);
    }

    public function openPaymentModal(): void
    {
        if (empty($this->cart)) return;
        $this->amountReceived   = $this->getCartTotal();
        $this->showPaymentModal = true;
        $this->dispatch('focus-amount');
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->dispatch('focus-search');
    }

    public function confirmSale(): void
    {
        if (empty($this->cart)) return;

        $total      = $this->getCartTotal();
        $ctotal     = $this->getCartCosto();
        $paygs      = $this->paymentMethod === 'efectivo'      ? $total : 0;
        $paytr      = $this->paymentMethod === 'transferencia' ? $total : 0;
        $paytj      = $this->paymentMethod === 'tarjeta'       ? $total : 0;
        $cart       = $this->cart;
        $depositoId = $this->depositoId;
        $cajeroId   = $this->cajeroId;
        $clienteId  = $this->clienteId;

        try {
            DB::connection('delphi')->transaction(function () use (
                $total, $ctotal, $paygs, $paytr, $paytj,
                $cart, $depositoId, $cajeroId, $clienteId
            ) {
                $hoy = today()->toDateString();
                $hora = now()->format('H:i:s');

                // 1. Cabecera de factura
                DB::connection('delphi')->table('invo1')->insert([
                    'total'       => $total,
                    'ctotal'      => $ctotal,
                    'flag'        => '4',
                    'fecha'       => $hoy,
                    'hora'        => $hora,
                    'pernr'       => $cajeroId,
                    'custnr'      => $clienteId,
                    'depo'        => $depositoId,
                    'saldo'       => 0,
                    'sta_num'     => '01',
                    'sucursal_id' => 1,
                ]);

                // 2. Recuperar invnr como string (tiene ceros adelante, generado por trigger)
                $invnr = DB::connection('delphi')
                    ->table('invo1')
                    ->where('fecha',   $hoy)
                    ->where('hora',    $hora)
                    ->where('pernr',   $cajeroId)
                    ->where('custnr',  $clienteId)
                    ->where('total',   $total)
                    ->orderByRaw('invnr DESC')
                    ->value('invnr');

                // 3. Detalle de factura (invnr como string)
                foreach ($cart as $item) {
                    DB::connection('delphi')->table('invo2')->insert([
                        'invnr'  => (string) $invnr,
                        'item'   => $item['item'],
                        'descr'  => $item['descr'],
                        'depo'   => $depositoId,
                        'qty'    => $item['qty'],
                        'precio' => $item['precio'],
                        'costo'  => $item['costo'],
                        'flag'   => '3',
                        'fecha'  => $hoy,
                    ]);
                }

                // 4. Descontar stock en items e itemsdepo
                foreach ($cart as $item) {
                    DB::connection('delphi')->table('items')
                        ->where('item', $item['item'])
                        ->decrement('stock', $item['qty']);

                    DB::connection('delphi')->table('itemsdepo')
                        ->where('item', $item['item'])
                        ->where('depo', $depositoId)
                        ->decrement('qty', $item['qty']);
                }

                // 5. Registrar movimiento de caja
                DB::connection('delphi')->table('cajareg')->insert([
                    'fecha'       => $hoy,
                    'totalus'     => $total,
                    'total'       => $total,
                    'paygs'       => $paygs,
                    'paytr'       => $paytr,
                    'paytj'       => $paytj,
                    'ctotal'      => $ctotal,
                    'sta_num'     => '01',
                    'op'          => 'PC',
                    'flag'        => 1,
                    'tipo_moneda' => 'GS',
                ]);

                // 6. Obtener el nro (PK) del registro insertado, luego leer el cajanr
                //    que generó el trigger (cajanr es un campo separado del PK)
                $nro    = DB::connection('delphi')->getPdo()->lastInsertId();
                $cajanr = DB::connection('delphi')
                    ->table('cajareg')
                    ->where('nro', $nro)
                    ->value('cajanr');

                DB::connection('delphi')->table('invo1')
                    ->where('invnr', $invnr)
                    ->update(['cajanr' => $cajanr]);
            });

            $this->cart             = [];
            $this->showPaymentModal = false;
            $this->search           = '';
            $this->searchResults    = [];
            $this->dispatch('focus-search');

            Notification::make()->title('Venta registrada correctamente')->success()->send();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error al registrar la venta')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
