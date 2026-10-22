<?php

namespace App\Filament\Pages;

use App\Traits\CheckUserAcceso;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class PosPage extends Page
{
    use CheckUserAcceso;
    protected static string  $view            = 'filament.pages.pos-page';

    public function getView(): string
    {
        $tipo = auth()->user()?->empresa?->tipo ?? 'general';
        return $tipo === 'supermercado'
            ? 'filament.pages.pos-page-minimal'
            : 'filament.pages.pos-page';
    }

    protected static ?string $navigationIcon  = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Ventas';
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?string $title           = 'Ventas';
    protected static ?int    $navigationSort  = 1;

    // Búsqueda
    public string $search           = '';
    public array  $searchResults    = [];
    public mixed  $categoriaId      = null;

    // Categorías
    public array  $categorias       = [];

    // Selección de contexto
    public mixed  $clienteId  = null;
    public mixed  $depositoId = null;
    public mixed  $cajeroId   = null;
    public bool   $cajeroLocked = false;

    // Carrito
    public array $cart = [];

    // Modal de pago
    public bool   $showPaymentModal = false;
    public string $paymentMethod    = 'efectivo';
    public float  $amountReceived   = 0;
    public float  $amountQr        = 0;
    public mixed  $lastInvoiceNumber = null;
    public float  $lastTotal         = 0;
    public float  $lastReceived      = 0;
    public string $lastPaymentMethod = 'efectivo';

    // Modal de nuevo cliente
    public bool   $showNewClienteModal = false;
    public mixed  $editingClienteId    = null;
    public string $newClienteName      = '';
    public string $newClienteNacionalidad = '';
    public string $newClienteDireccion = '';
    public string $newClienteTelefono  = '';
    public string $newClienteEmail     = '';
    public string $newClienteRuc       = '';
    public string $newClienteIdentidad = '';

    // Combos para los selects
    public array  $clientes  = [];
    public array  $depositos = [];
    public array  $cajeros   = [];
    public string $clienteName = 'Ocasional';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('fullscreen')
                ->label('Pantalla completa')
                ->icon('heroicon-o-arrows-pointing-out')
                ->color('gray')
                ->outlined()
                ->extraAttributes([
                    'onclick' => "document.body.classList.toggle('pos-fullscreen');
                                  if (document.body.classList.contains('pos-fullscreen')) {
                                      try { document.documentElement.requestFullscreen?.(); } catch(e) {}
                                  } else {
                                      try { document.exitFullscreen?.(); } catch(e) {}
                                  }
                                  return false;"
                ]),
        ];
    }

    public function mount(): void
    {
        $this->showNewClienteModal = false;

        // Ocultar la barra lateral automáticamente en POS
        $this->dispatch('sidebar-collapsed');

        $this->categorias = DB::connection('delphi')
            ->table('grupo')
            ->select('grupnr', 'descr')
            ->orderBy('descr')
            ->get()
            ->map(fn ($g) => (object)[
                'grupnr' => $g->grupnr,
                'descr'  => mb_convert_encoding((string)($g->descr ?? ''), 'UTF-8', 'UTF-8'),
            ])
            ->toArray();

        $this->clientes = DB::connection('delphi')
            ->table('cliente')
            ->select('custnr', 'custname', 'ruc', 'email')
            ->orderByRaw("CASE WHEN custname LIKE '%OCASIONAL%' THEN 0 WHEN custname IS NOT NULL AND custname != '' THEN 1 ELSE 2 END, custname")
            ->get()
            ->map(fn ($c) => (object)[
                'custnr'   => $c->custnr,
                'custname' => mb_convert_encoding((string)($c->custname ?? ''), 'UTF-8', 'UTF-8'),
                'ruc'      => (string)($c->ruc ?? ''),
                'email'    => (string)($c->email ?? ''),
            ])
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

        // Cliente por defecto: buscar "OCASIONAL", si no existe usar el primero
        $ocasional = collect($this->clientes)->first(fn ($c) =>
            stripos(trim($c->custname ?? ''), 'OCASIONAL') !== false
        );
        if ($ocasional) {
            $this->clienteId  = $ocasional->custnr;
            $this->clienteName = $ocasional->custname;
        } elseif ($this->clientes) {
            $this->clienteId  = $this->clientes[0]->custnr;
            $this->clienteName = $this->clientes[0]->custname;
        }

        // Depósito por defecto: D01
        $this->depositoId = 'D01';

        // Vendedor: si el usuario tiene uno asignado, usarlo y bloquearlo
        $userPersonalId = auth()->user()?->personal_id;
        if ($userPersonalId) {
            $userVendedor = collect($this->cajeros)->first(fn ($c) => $c->pernr == $userPersonalId);
            if ($userVendedor) {
                $this->cajeroId = $userVendedor->pernr;
                $this->cajeroLocked = true;
            } elseif ($this->cajeros) {
                $this->cajeroId = $this->cajeros[0]->pernr;
                $this->cajeroLocked = false;
            }
        } elseif ($this->cajeros) {
            $this->cajeroId = $this->cajeros[0]->pernr;
            $this->cajeroLocked = false;
        }
    }

    public function updatedSearch(): void
    {
        $term = trim($this->search);

        if (strlen($term) < 2) {
            $this->searchResults = [];
            return;
        }

        $words = preg_split('/\s+/', $term);
        $mostrarProductosSinStock = auth()->user()?->empresa?->mostrar_productos_sin_stock ?? true;

        $query = DB::connection('delphi')
            ->table('items')
            ->select('items.item', 'items.scode', 'items.scode1', 'items.descr', 'items.precio3 as precio', 'items.costo')
            ->selectRaw(
                'COALESCE((SELECT qty FROM itemsdepo WHERE itemsdepo.item = items.item AND itemsdepo.depo = ?), 0) AS stock_depo',
                [$this->depositoId]
            )
            ->whereRaw("TRIM(COALESCE(items.item, '')) <> ''")
            ->when(!$mostrarProductosSinStock, fn ($q) => $q->whereRaw(
                'COALESCE((SELECT qty FROM itemsdepo WHERE itemsdepo.item = items.item AND itemsdepo.depo = ?), 0) > 0',
                [$this->depositoId]
            ));

        // Un término numérico largo es un código de barras (lector USB o cámara): se busca en todo el
        // catálogo, sin importar la categoría elegida, y tolerando ceros a la izquierda (UPC-A / EAN-13)
        $esCodigo   = ctype_digit($term) && strlen($term) >= 8;
        $candidatos = [$term];
        if ($esCodigo) {
            $sinCeros   = ltrim($term, '0');
            $candidatos = array_values(array_unique([$term, $sinCeros, '0' . $sinCeros, '00' . $sinCeros]));
        }

        // Filtrar por categoría si está seleccionada (no aplica a códigos de barras)
        if ($this->categoriaId !== null && !$esCodigo) {
            $query->where('items.grupnr', $this->categoriaId);
        }

        if ($esCodigo) {
            $query->where(function ($q) use ($candidatos, $term) {
                $q->whereIn('items.scode1', $candidatos)
                  ->orWhereIn('items.scode', $candidatos)
                  ->orWhereIn('items.item', $candidatos)
                  ->orWhereRaw("CONCAT_WS(' ', items.item, items.scode, items.scode1, items.descr) LIKE ?", ['%' . $term . '%']);
            });
        } else {
            foreach ($words as $word) {
                $query->whereRaw(
                    "CONCAT_WS(' ', items.item, items.scode, items.scode1, items.descr) LIKE ?",
                    ['%' . $word . '%']
                );
            }
        }

        $results = $query
            ->when(!$mostrarProductosSinStock, fn ($q) => $q->where('items.stock', '>', 0))
            ->orderBy('items.descr')
            ->limit(12)
            ->get()
            ->toArray();

        // Si el término coincide exactamente con item, scode o scode1 → agregar al carrito automáticamente
        $lowerCandidatos = array_map('strtolower', $candidatos);
        $exact = collect($results)->first(fn ($r) =>
            in_array(strtolower((string) ($r->item   ?? '')), $lowerCandidatos, true) ||
            in_array(strtolower((string) ($r->scode  ?? '')), $lowerCandidatos, true) ||
            in_array(strtolower((string) ($r->scode1 ?? '')), $lowerCandidatos, true)
        );

        if ($exact) {
            $this->addToCartItem($exact);
            return;
        }

        if ($esCodigo && empty($results)) {
            Notification::make()
                ->title('Código no encontrado')
                ->body("No hay ningún producto con el código {$term}.")
                ->warning()
                ->send();
        }

        $this->searchResults = $results;
    }

    public function updatedCategoriaId(): void
    {
        $this->search = '';
        $this->loadCategoryProducts();
    }

    public function updatedClienteId(): void
    {
        $cliente = collect($this->clientes)->firstWhere('custnr', $this->clienteId);
        $this->clienteName = $cliente ? $cliente->custname : 'Ocasional';
    }

    private function loadCategoryProducts(): void
    {
        $mostrarProductosSinStock = auth()->user()?->empresa?->mostrar_productos_sin_stock ?? true;

        $query = DB::connection('delphi')
            ->table('items')
            ->select('items.item', 'items.scode', 'items.scode1', 'items.descr', 'items.precio3 as precio', 'items.costo')
            ->selectRaw(
                'COALESCE((SELECT qty FROM itemsdepo WHERE itemsdepo.item = items.item AND itemsdepo.depo = ?), 0) AS stock_depo',
                [$this->depositoId]
            )
            // Se ignoran filas sin código (basura en la base): no se pueden vender ni tienen foto
            ->whereRaw("TRIM(COALESCE(items.item, '')) <> ''");

        // Si hay una categoría seleccionada, filtrar por ella
        if ($this->categoriaId !== null) {
            $query->where('items.grupnr', $this->categoriaId);
        }

        // Aplicar filtro de stock si está desactivado
        if (!$mostrarProductosSinStock) {
            $query->whereRaw(
                'COALESCE((SELECT qty FROM itemsdepo WHERE itemsdepo.item = items.item AND itemsdepo.depo = ?), 0) > 0',
                [$this->depositoId]
            );
        }

        $results = $query
            ->orderBy('items.descr')
            ->limit(12)
            ->get()
            ->toArray();

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

        // Validar stock si la configuración lo requiere
        $permitirVentaSinStock = auth()->user()?->empresa?->permitir_venta_sin_stock ?? false;
        if (!$permitirVentaSinStock && $item->stock_depo <= 0) {
            Notification::make()
                ->title('Sin stock disponible')
                ->body("El producto {$item->descr} no tiene stock disponible.")
                ->warning()
                ->send();
            return;
        }

        $idx = collect($this->cart)->search(fn($c) => $c['item'] === $itemCode);

        if ($idx !== false) {
            // Si el producto ya está en el carrito, validar que no se exceda el stock
            $nuevaCantidad = $this->cart[$idx]['qty'] + 1;
            if (!$permitirVentaSinStock && $nuevaCantidad > $item->stock_depo) {
                Notification::make()
                    ->title('Stock insuficiente')
                    ->body("Solo hay {$item->stock_depo} unidades de {$item->descr} disponibles. Ya hay {$this->cart[$idx]['qty']} en el carrito.")
                    ->warning()
                    ->send();
                return;
            }
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

        // Mobile: limpia búsqueda para el siguiente ítem
        $this->search        = '';
        $this->searchResults = [];
        $this->dispatch('focus-search');
    }

    // Desktop/tablet: agrega al carrito, limpia el input pero mantiene la grilla visible
    public function addToCartKeepSearch(string $itemCode): void
    {
        $item = collect($this->searchResults)->first(fn($r) => $r->item === $itemCode);
        if (!$item) return;

        // Validar stock si la configuración lo requiere
        $permitirVentaSinStock = auth()->user()?->empresa?->permitir_venta_sin_stock ?? false;
        if (!$permitirVentaSinStock && $item->stock_depo <= 0) {
            Notification::make()
                ->title('Sin stock disponible')
                ->body("El producto {$item->descr} no tiene stock disponible.")
                ->warning()
                ->send();
            return;
        }

        $idx = collect($this->cart)->search(fn($c) => $c['item'] === $itemCode);

        if ($idx !== false) {
            // Si el producto ya está en el carrito, validar que no se exceda el stock
            $nuevaCantidad = $this->cart[$idx]['qty'] + 1;
            if (!$permitirVentaSinStock && $nuevaCantidad > $item->stock_depo) {
                Notification::make()
                    ->title('Stock insuficiente')
                    ->body("Solo hay {$item->stock_depo} unidades de {$item->descr} disponibles. Ya hay {$this->cart[$idx]['qty']} en el carrito.")
                    ->warning()
                    ->send();
                return;
            }
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

        // Limpia el input (sin disparar updatedSearch, así la grilla queda visible)
        $this->search = '';
        $this->dispatch('focus-search');
    }

    /**
     * Reconstruye un carrito guardado en el navegador (por ejemplo tras recargar la página),
     * revalidando cada línea contra la base: existencia, stock y precio mínimo permitido.
     */
    public function restoreCart(array $saved, mixed $clienteId = null, mixed $depositoId = null): void
    {
        if (!empty($this->cart) || empty($saved)) return;

        if ($depositoId !== null && collect($this->depositos)->contains(fn ($d) => (string) $d->deponr === (string) $depositoId)) {
            $this->depositoId = $depositoId;
        }

        if ($clienteId !== null) {
            $cliente = collect($this->clientes)->first(fn ($c) => (string) $c->custnr === (string) $clienteId);
            if ($cliente) {
                $this->clienteId   = $cliente->custnr;
                $this->clienteName = $cliente->custname;
                $this->dispatch('clienteActualizado', clienteId: $cliente->custnr, clienteName: $cliente->custname, clienteRuc: $cliente->ruc ?? '');
            }
        }

        $user    = auth()->user();
        $permitirSinStock = $user?->empresa?->permitir_venta_sin_stock ?? false;
        $nivel   = (int) ($user?->precio_minimo_permitido ?? 3);
        $nivel   = in_array($nivel, [1, 2, 3], true) ? $nivel : 3;

        $cart = [];
        $quitados = 0;
        $ajustados = 0;

        foreach (array_slice($saved, 0, 200) as $line) {
            $code = is_array($line) ? (string) ($line['item'] ?? '') : '';
            if ($code === '') continue;

            $row = DB::connection('delphi')->table('items')
                ->where('item', $code)
                ->selectRaw(
                    'item, descr, costo, precio1, precio2, precio3, COALESCE((SELECT qty FROM itemsdepo WHERE itemsdepo.item = items.item AND itemsdepo.depo = ?), 0) AS stock_depo',
                    [$this->depositoId]
                )
                ->first();

            if (!$row) { $quitados++; continue; }

            $qty   = max(1, (int) ($line['qty'] ?? 1));
            $stock = (int) $row->stock_depo;

            if (!$permitirSinStock) {
                if ($stock <= 0) { $quitados++; continue; }
                if ($qty > $stock) { $qty = $stock; $ajustados++; }
            }

            $minimo = (float) $row->{'precio' . $nivel};
            $precioGuardado = (float) ($line['precio'] ?? $row->precio3);
            $precio = max($minimo, $precioGuardado);
            if ($precio !== $precioGuardado) $ajustados++;

            $cart[] = [
                'item'       => $row->item,
                'descr'      => $row->descr,
                'qty'        => $qty,
                'precio'     => $precio,
                'costo'      => (float) $row->costo,
                'stock_depo' => $stock,
            ];
        }

        if (empty($cart)) return;

        $this->cart = $cart;

        $detalle = [];
        if ($quitados)  $detalle[] = "{$quitados} producto(s) ya no estaban disponibles";
        if ($ajustados) $detalle[] = "{$ajustados} línea(s) se ajustaron por stock o precio";

        Notification::make()
            ->title('Carrito recuperado')
            ->body(count($cart) . ' producto(s) restaurados.' . ($detalle ? ' ' . implode('; ', $detalle) . '.' : ''))
            ->info()
            ->send();
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

    public function validateAndUpdateCartItem(int $index, int $qty, float $precio): void
    {
        if (!isset($this->cart[$index])) return;

        // Obtener el item de la BD para verificar sus precios
        $item = DB::connection('delphi')
            ->table('items')
            ->where('item', $this->cart[$index]['item'])
            ->select('precio1', 'precio2', 'precio3')
            ->first();

        if (!$item) {
            Notification::make()->title('Error')->body('Producto no encontrado')->danger()->send();
            return;
        }

        // Obtener el nivel de precio mínimo permitido del usuario (default 3)
        $precioMinimoPermitido = auth()->user()?->precio_minimo_permitido ?? 3;

        // Mapear el nivel a los precios disponibles: 1->precio1, 2->precio2, 3->precio3
        $preciosPermitidos = [
            1 => $item->precio1,
            2 => $item->precio2,
            3 => $item->precio3,
        ];

        $precioMinimo = $preciosPermitidos[$precioMinimoPermitido];

        // Validar que el precio ingresado no sea menor que el permitido
        if ($precio < $precioMinimo) {
            Notification::make()
                ->title('Precio no permitido')
                ->body("Este usuario solo puede vender a partir de Gs. " . number_format($precioMinimo, 0, ',', '.') . " (Precio $precioMinimoPermitido)")
                ->warning()
                ->send();
            return;
        }

        // Si es válido, actualizar el carrito
        $this->cart[$index]['qty']    = max(1, $qty);
        $this->cart[$index]['precio'] = max($precioMinimo, $precio);
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

        // Si no hay módulo de caja, procesar venta directamente
        $moduloCajaEnabled = auth()->user()?->empresa?->modulo_caja_enabled ?? true;
        if (!$moduloCajaEnabled) {
            $this->confirmSale();
            return;
        }

        $this->amountReceived   = $this->getCartTotal();
        $this->amountQr         = 0;
        $this->showPaymentModal = true;
        $this->dispatch('focus-amount');
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->amountQr         = 0;
        $this->lastInvoiceNumber = null;
        $this->lastTotal         = 0;
        $this->lastReceived      = 0;
        $this->dispatch('focus-search');
    }

    public function openNewClienteModal(): void
    {
        $this->showNewClienteModal = true;
        $this->newClienteName = '';
        $this->newClienteNacionalidad = '';
        $this->newClienteDireccion = '';
        $this->newClienteTelefono = '';
        $this->newClienteEmail = '';
        $this->newClienteRuc = '';
        $this->newClienteIdentidad = '';
    }

    public function openEditClienteModal(): void
    {
        if (!$this->clienteId) {
            Notification::make()->title('Selecciona un cliente primero')->warning()->send();
            return;
        }

        $cliente = DB::connection('delphi')
            ->table('cliente')
            ->where('custnr', $this->clienteId)
            ->first();

        if ($cliente) {
            $this->showNewClienteModal = true;
            $this->newClienteName = $cliente->custname ?? '';
            $this->newClienteNacionalidad = $cliente->pais ?? '';
            $this->newClienteDireccion = $cliente->addr ?? '';
            $this->newClienteTelefono = $cliente->telef1 ?? '';
            $this->newClienteEmail = $cliente->email ?? '';
            $this->newClienteRuc = $cliente->ruc ?? '';
            $this->newClienteIdentidad = $cliente->identidad ?? '';
            $this->editingClienteId = $this->clienteId;
        }
    }

    public function closeNewClienteModal(): void
    {
        $this->showNewClienteModal = false;
        $this->editingClienteId = null;
        $this->newClienteName = '';
        $this->newClienteNacionalidad = '';
        $this->newClienteDireccion = '';
        $this->newClienteTelefono = '';
        $this->newClienteEmail = '';
        $this->newClienteRuc = '';
        $this->newClienteIdentidad = '';
    }

    public function saveNewCliente(): void
    {
        if (empty(trim($this->newClienteName))) {
            Notification::make()->title('Nombre requerido')->danger()->send();
            return;
        }

        try {
            if (!empty($this->editingClienteId)) {
                // UPDATE cliente existente
                $newClienteCode = $this->editingClienteId;
                DB::connection('delphi')->table('cliente')
                    ->where('custnr', $newClienteCode)
                    ->update([
                        'custname'  => $this->newClienteName,
                        'email'     => $this->newClienteEmail ?: null,
                        'pais'      => $this->newClienteNacionalidad ?: null,
                        'addr'      => $this->newClienteDireccion ?: null,
                        'telef1'    => $this->newClienteTelefono ?: null,
                        'ruc'       => $this->newClienteRuc ?: null,
                        'identidad' => $this->newClienteIdentidad ?: null,
                    ]);

                Notification::make()->title('Cliente actualizado correctamente')->success()->send();
            } else {
                // INSERT nuevo cliente
                $maxCode = DB::connection('delphi')->table('cliente')->max('custnr');
                $newClienteCode = ($maxCode ? (int)$maxCode : 0) + 1;

                DB::connection('delphi')->table('cliente')->insert([
                    'custname' => $this->newClienteName,
                    'custnr'   => $newClienteCode,
                    'email'    => $this->newClienteEmail ?: null,
                    'pais'     => $this->newClienteNacionalidad ?: null,
                    'addr'     => $this->newClienteDireccion ?: null,
                    'telef1'   => $this->newClienteTelefono ?: null,
                    'ruc'      => $this->newClienteRuc ?: null,
                    'identidad'=> $this->newClienteIdentidad ?: null,
                ]);

                Notification::make()->title('Cliente registrado correctamente')->success()->send();
            }

            // Limpiar el modo edición INMEDIATAMENTE después de la operación
            $this->editingClienteId = null;

            // Recargar la lista de clientes
            $this->clientes = DB::connection('delphi')
                ->table('cliente')
                ->select('custnr', 'custname', 'ruc', 'email')
                ->orderBy('custname')
                ->get()
                ->map(fn ($c) => (object)[
                    'custnr'   => $c->custnr,
                    'custname' => mb_convert_encoding((string)($c->custname ?? ''), 'UTF-8', 'UTF-8'),
                    'ruc'      => (string)($c->ruc ?? ''),
                    'email'    => (string)($c->email ?? ''),
                ])
                ->toArray();

            // Buscar y seleccionar el cliente
            $newCliente = collect($this->clientes)->first(fn ($c) =>
                (string)$c->custnr === (string)$newClienteCode
            );

            if ($newCliente) {
                $this->clienteId = $newCliente->custnr;
                $this->clienteName = $newCliente->custname;
            }

            $this->showNewClienteModal = false;
            $this->dispatch(
                'clienteActualizado',
                clienteId: $newClienteCode,
                clienteName: $newCliente->custname ?? $this->newClienteName,
                clienteRuc: $newCliente->ruc ?? $this->newClienteRuc,
            );

            // Recargar los datos del cliente para que se vean los cambios si se abre el modal nuevamente
            $clienteActualizado = DB::connection('delphi')
                ->table('cliente')
                ->where('custnr', $newClienteCode)
                ->first();

            if ($clienteActualizado) {
                $this->newClienteName = $clienteActualizado->custname ?? '';
                $this->newClienteNacionalidad = $clienteActualizado->pais ?? '';
                $this->newClienteDireccion = $clienteActualizado->addr ?? '';
                $this->newClienteTelefono = $clienteActualizado->telef1 ?? '';
                $this->newClienteEmail = $clienteActualizado->email ?? '';
                $this->newClienteRuc = $clienteActualizado->ruc ?? '';
                $this->newClienteIdentidad = $clienteActualizado->identidad ?? '';
            } else {
                // Si no se encuentra el cliente, limpiar los campos
                $this->newClienteName = '';
                $this->newClienteNacionalidad = '';
                $this->newClienteDireccion = '';
                $this->newClienteTelefono = '';
                $this->newClienteEmail = '';
                $this->newClienteRuc = '';
                $this->newClienteIdentidad = '';
            }
        } catch (\Exception $e) {
            Notification::make()->title('Error al registrar cliente')->body($e->getMessage())->danger()->send();
        }
    }

    public function confirmSale(): void
    {
        if (empty($this->cart)) return;

        $total      = $this->getCartTotal();
        $ctotal     = $this->getCartCosto();
        $paygs      = $this->paymentMethod === 'efectivo'                           ? $total : 0;
        $paytr      = in_array($this->paymentMethod, ['transferencia', 'qr'])       ? $total : 0;
        $paytj      = $this->paymentMethod === 'tarjeta'                            ? $total : 0;
        $cart       = $this->cart;
        $depositoId = $this->depositoId;
        $cajeroId   = $this->cajeroId;
        $clienteId  = $this->clienteId;
        $moduloCajaEnabled = auth()->user()?->empresa?->modulo_caja_enabled ?? true;

        try {
            $invnr = null;
            DB::connection('delphi')->transaction(function () use (
                $total, $ctotal, $paygs, $paytr, $paytj,
                $cart, $depositoId, $cajeroId, $clienteId, $moduloCajaEnabled, &$invnr
            ) {
                $hoy = today()->toDateString();
                $hora = now()->format('H:i:s');

                // 1. Cabecera de factura
                DB::connection('delphi')->table('invo1')->insert([
                    'total'       => $total,
                    'totalv'      => $total,
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

                // 5. Registrar movimiento de caja SOLO SI MÓDULO ACTIVADO
                if ($moduloCajaEnabled) {
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

                    // 6. Obtener cajanr solo si cajareg fue creado
                    $nro    = DB::connection('delphi')->getPdo()->lastInsertId();
                    $cajanr = DB::connection('delphi')
                        ->table('cajareg')
                        ->where('nro', $nro)
                        ->value('cajanr');

                    DB::connection('delphi')->table('invo1')
                        ->where('invnr', $invnr)
                        ->update(['cajanr' => $cajanr]);
                }
            });

            // Guardar datos de la venta para ofrecer la impresión del ticket
            $this->lastTotal         = $total;
            $this->lastPaymentMethod = $this->paymentMethod;
            $this->lastReceived      = $this->paymentMethod === 'efectivo' && $this->amountReceived > 0
                ? (float) $this->amountReceived
                : $total;
            $this->lastInvoiceNumber = $invnr;

            $this->cart             = [];
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

    public static function canAccess(): bool
    {
        return self::userHasAcceso('ventas');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }
}
