<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HistorialVentasResource\Pages;
use App\Models\Venta;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\DatePicker;
use App\Traits\CheckUserAcceso;
use App\Models\Empresa;
use App\Models\WaPedido;
use App\Support\Modulos;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Support\Carbon;

class HistorialVentasResource extends Resource
{
    use CheckUserAcceso;

    protected static ?string $model           = Venta::class;
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Historial de Ventas';
    protected static ?string $modelLabel      = 'Venta';
    protected static ?string $pluralModelLabel = 'Historial de Ventas';
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?int    $navigationSort  = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select(
                'nro',
                'invnr',
                'fecha',
                'nro_factura',
                'custnr',
                'cajanr',
                'total',
                'saldo',
                'acredito',
                'pernr',
                'hora',
                DB::connection('delphi')->raw('(SELECT custname FROM cliente WHERE cliente.custnr = invo1.custnr LIMIT 1) as custname'),
                DB::connection('delphi')->raw('(SELECT pername FROM personal WHERE personal.pernr = invo1.pernr LIMIT 1) as vendedor'),
                DB::connection('delphi')->raw("(SELECT COALESCE(SUM(paygs),0) FROM cajareg WHERE cajareg.cajanr = invo1.cajanr AND invo1.cajanr <> '') as pago_efectivo"),
                DB::connection('delphi')->raw("(SELECT COALESCE(SUM(payTR),0) FROM cajareg WHERE cajareg.cajanr = invo1.cajanr AND invo1.cajanr <> '') as pago_transf"),
                DB::connection('delphi')->raw("(SELECT COALESCE(SUM(paytj),0) FROM cajareg WHERE cajareg.cajanr = invo1.cajanr AND invo1.cajanr <> '') as pago_tarjeta")
            )
            ->where('flag', '4')
            ->orderByDesc('fecha')
            ->orderByDesc('invnr');
    }

    private const ORIGENES = [
        'pos'     => 'Punto de venta',
        'agente'  => 'Agente IA',
        'tienda'  => 'Tienda web',
    ];

    /**
     * Ventas que nacieron de un pedido de WhatsApp: invnr => [id del pedido, origen]. Se deduce de los pedidos del
     * agente (base local), así no hace falta ningún campo nuevo en invo1 ni tocar la base del cliente; toda venta que
     * no vino de un pedido es del punto de venta.
     *
     * @return array<string, array{id: int, origen: string}>
     */
    public static function ventasDeWhatsApp(): array
    {
        static $cache = [];

        $empresaId = auth()->user()?->empresa_id
            ?: Empresa::where('db_name', config('database.connections.delphi.database'))->value('id');
        if (!$empresaId) {
            return [];
        }

        return $cache[$empresaId] ??= WaPedido::where('empresa_id', $empresaId)->whereNotNull('venta_invnr')->where('estado', 'pagado')
            ->get(['id', 'venta_invnr', 'origen'])
            ->mapWithKeys(fn ($p) => [(string) $p->venta_invnr => ['id' => $p->id, 'origen' => $p->origen === 'tienda_web' ? 'tienda' : 'agente']])
            ->all();
    }

    public static function origenDe($record): string
    {
        return self::ventasDeWhatsApp()[(string) $record->invnr]['origen'] ?? 'pos';
    }

    private static function gs($v): string
    {
        return 'Gs. ' . number_format((float) $v, 0, ',', '.');
    }

    private static function formaPago($r): string
    {
        $partes = [];
        if ((float) $r->pago_efectivo > 0) $partes[] = 'Efectivo';
        if ((float) $r->pago_transf > 0)   $partes[] = 'Transferencia';
        if ((float) $r->pago_tarjeta > 0)  $partes[] = 'Tarjeta';
        if ((float) $r->acredito > 0)      $partes[] = 'Crédito';

        return $partes ? implode(' + ', $partes) : '—';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invnr')
                    ->label('Nro. Venta')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->description(fn ($record) => $record->hora ? substr((string) $record->hora, 0, 5) : null)
                    ->sortable(),

                TextColumn::make('origen')
                    ->label('Origen')
                    ->badge()
                    ->getStateUsing(fn ($record) => self::origenDe($record))
                    ->formatStateUsing(fn ($state) => self::ORIGENES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) { 'agente' => 'success', 'tienda' => 'info', default => 'gray' })
                    ->icon(fn ($state) => match ($state) { 'agente' => 'heroicon-o-chat-bubble-left-right', 'tienda' => 'heroicon-o-shopping-bag', default => 'heroicon-o-computer-desktop' }),

                TextColumn::make('nro_factura')
                    ->label('Factura')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('custnr')
                    ->label('Cod.')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('custname')
                    ->label('Cliente')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Gs. ' . number_format($state, 0, ',', '.'))
                    ->alignRight()
                    ->sortable()
                    ->summarize([
                        Count::make()->label('Ventas'),
                        Sum::make()->label('Total vendido')->formatStateUsing(fn ($state) => self::gs($state)),
                    ]),

                TextColumn::make('vendedor')
                    ->label('Vendedor')
                    ->placeholder('—')
                    ->limit(20)
                    ->toggleable(),

                TextColumn::make('forma_pago')
                    ->label('Pago')
                    ->getStateUsing(fn ($record) => self::formaPago($record))
                    ->toggleable(),

                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->formatStateUsing(fn ($state) => $state > 0 ? 'Gs. ' . number_format($state, 0, ',', '.') : '—')
                    ->alignRight()
                    ->color(fn ($state) => $state > 0 ? 'danger' : null),

                TextColumn::make('condicion')
                    ->label('Condición')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->acredito > 0 ? 'credito' : 'contado')
                    ->formatStateUsing(fn ($state) => $state === 'credito' ? 'Crédito' : 'Contado')
                    ->color(fn ($state) => $state === 'credito' ? 'warning' : 'info'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->acredito > 0) {
                            return $record->saldo == 0 ? 'pagado' : 'pendiente';
                        }
                        return $record->cajanr ? 'pagado' : 'pendiente';
                    })
                    ->formatStateUsing(fn ($state) => $state === 'pagado' ? 'Pagado' : 'Pendiente')
                    ->color(fn ($state) => $state === 'pagado' ? 'success' : 'danger'),

                TextColumn::make('cajanr')
                    ->label('Caja Nro.')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('periodo')
                    ->label('Período')
                    ->options(['hoy' => 'Hoy', 'ayer' => 'Ayer', 'semana' => 'Esta semana', 'mes' => 'Este mes', 'mes_pasado' => 'Mes pasado'])
                    ->default('mes')
                    ->query(function (Builder $query, array $data) {
                        $hoy = Carbon::today();
                        match ($data['value'] ?? null) {
                            'hoy'        => $query->whereDate('fecha', $hoy),
                            'ayer'       => $query->whereDate('fecha', $hoy->copy()->subDay()),
                            'semana'     => $query->whereBetween('fecha', [$hoy->copy()->startOfWeek()->toDateString(), $hoy->toDateString()]),
                            'mes'        => $query->whereBetween('fecha', [$hoy->copy()->startOfMonth()->toDateString(), $hoy->toDateString()]),
                            'mes_pasado' => $query->whereBetween('fecha', [$hoy->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(), $hoy->copy()->subMonthNoOverflow()->endOfMonth()->toDateString()]),
                            default      => null,
                        };
                    }),

                SelectFilter::make('origen')
                    ->label('Origen de la venta')
                    ->options(self::ORIGENES)
                    ->query(function (Builder $query, array $data) {
                        $valor = $data['value'] ?? null;
                        if (!$valor) return;
                        $wa = self::ventasDeWhatsApp();
                        if ($valor === 'pos') {
                            if ($wa) $query->whereNotIn('invnr', array_map('strval', array_keys($wa)));
                            return;
                        }
                        $ids = array_keys(array_filter($wa, fn ($v) => $v['origen'] === $valor));
                        $ids ? $query->whereIn('invnr', array_map('strval', $ids)) : $query->whereRaw('1 = 0');
                    }),

                SelectFilter::make('vendedor')
                    ->label('Vendedor')
                    ->options(fn () => DB::connection('delphi')->table('personal')->orderBy('pername')->pluck('pername', 'pernr')->all())
                    ->query(fn (Builder $query, array $data) => ($data['value'] ?? null) ? $query->where('pernr', $data['value']) : null),

                SelectFilter::make('pago')
                    ->label('Forma de pago')
                    ->options(['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'tarjeta' => 'Tarjeta'])
                    ->query(function (Builder $query, array $data) {
                        $col = ['efectivo' => 'paygs', 'transferencia' => 'payTR', 'tarjeta' => 'paytj'][$data['value'] ?? ''] ?? null;
                        if (!$col) return;
                        $query->whereExists(fn ($q) => $q->select(DB::raw(1))->from('cajareg')->whereColumn('cajareg.cajanr', 'invo1.cajanr')->where("cajareg.$col", '>', 0));
                    }),

                Filter::make('fecha')
                    ->form([
                        DatePicker::make('desde')->label('Desde')->native(false),
                        DatePicker::make('hasta')->label('Hasta')->native(false),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['desde']) $query->whereDate('fecha', '>=', $data['desde']);
                        if ($data['hasta']) $query->whereDate('fecha', '<=', $data['hasta']);
                    }),

                SelectFilter::make('condicion')
                    ->label('Condición')
                    ->options([
                        'contado' => 'Contado',
                        'credito' => 'Crédito',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!$data['value']) return;
                        if ($data['value'] === 'contado') {
                            $query->where(fn ($q) => $q->whereNull('acredito')->orWhere('acredito', 0));
                        }
                        if ($data['value'] === 'credito') {
                            $query->where('acredito', '>', 0);
                        }
                    }),

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'pagado'    => 'Pagado',
                        'pendiente' => 'Pendiente',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!$data['value']) return;
                        if ($data['value'] === 'pagado') {
                            $query->where(function ($q) {
                                // Crédito pagado: acredito > 0 y saldo = 0
                                $q->where(fn ($s) => $s->where('acredito', '>', 0)->where('saldo', 0))
                                // Contado pagado: acredito = 0 y tiene cajanr
                                  ->orWhere(fn ($s) => $s->where(fn ($t) => $t->whereNull('acredito')->orWhere('acredito', 0))
                                                         ->whereNotNull('cajanr')->where('cajanr', '!=', ''));
                            });
                        }
                        if ($data['value'] === 'pendiente') {
                            $query->where(function ($q) {
                                // Crédito pendiente: acredito > 0 y saldo > 0
                                $q->where(fn ($s) => $s->where('acredito', '>', 0)->where('saldo', '>', 0))
                                // Contado pendiente: acredito = 0 y sin cajanr
                                  ->orWhere(fn ($s) => $s->where(fn ($t) => $t->whereNull('acredito')->orWhere('acredito', 0))
                                                         ->where(fn ($t) => $t->whereNull('cajanr')->orWhere('cajanr', '')));
                            });
                        }
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('imprimir')
                    ->label('Imprimir')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->url(fn ($record) => url('/comprobante/' . $record->invnr . '/ticket') . '?reimpresion=1')
                    ->openUrlInNewTab(),
                ViewAction::make()->label('Ver detalle'),
            ])
            ->defaultSort('fecha', 'desc')
            ->striped();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Origen de la venta')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('origen_venta')->label('Origen')->badge()
                            ->getStateUsing(fn ($record) => self::origenDe($record))
                            ->formatStateUsing(fn ($state) => self::ORIGENES[$state] ?? $state)
                            ->color(fn ($state) => match ($state) { 'agente' => 'success', 'tienda' => 'info', default => 'gray' }),
                        TextEntry::make('pedido_wa')->label('Pedido de WhatsApp')
                            ->getStateUsing(fn ($record) => ($p = self::ventasDeWhatsApp()[(string) $record->invnr] ?? null) ? 'PED-' . str_pad((string) $p['id'], 6, '0', STR_PAD_LEFT) : null)
                            ->placeholder('No viene de un pedido')
                            ->url(function ($record) {
                                $p = self::ventasDeWhatsApp()[(string) $record->invnr] ?? null;

                                return ($p && Modulos::permite(auth()->user(), 'whatsapp')) ? WaPedidoResource::getUrl('view', ['record' => $p['id']]) : null;
                            }),
                        TextEntry::make('vendedor')->label('Vendedor')->placeholder('—'),
                    ]),

                Section::make('Datos de la Venta')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('invnr')->label('Nro. Venta')->weight('bold'),
                        TextEntry::make('fecha')->label('Fecha')->date('d/m/Y'),
                        TextEntry::make('nro_factura')->label('Nro. Factura')->placeholder('—'),
                        TextEntry::make('cajanr')->label('Caja Nro.')->placeholder('—'),
                        TextEntry::make('custname')->label('Cliente'),
                        TextEntry::make('custnr')->label('Cód. Cliente'),
                        TextEntry::make('hora')->label('Hora')->formatStateUsing(fn ($state) => $state ? substr((string) $state, 0, 5) : '—'),
                        TextEntry::make('forma_pago')->label('Forma de pago')->getStateUsing(fn ($record) => self::formaPago($record)),
                        TextEntry::make('total')
                            ->label('Total')
                            ->formatStateUsing(fn ($state) => 'Gs. ' . number_format($state, 0, ',', '.')),
                        TextEntry::make('saldo')
                            ->label('Saldo Pendiente')
                            ->formatStateUsing(fn ($state) => $state > 0 ? 'Gs. ' . number_format($state, 0, ',', '.') : 'Sin saldo')
                            ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                    ]),

                Section::make('Ítems de la Venta')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('item')->label('Código'),
                                TextEntry::make('descr')->label('Descripción'),
                                TextEntry::make('qty')
                                    ->label('Cantidad')
                                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')),
                                TextEntry::make('precio')
                                    ->label('Precio Unit.')
                                    ->formatStateUsing(fn ($state) => 'Gs. ' . number_format($state, 0, ',', '.')),
                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->weight('bold')
                                    ->formatStateUsing(fn ($state) => 'Gs. ' . number_format($state, 0, ',', '.')),
                            ])
                            ->columns(5)
                            ->getStateUsing(function ($record) {
                                return DB::connection('delphi')
                                    ->table('invo2')
                                    ->where('invnr', $record->invnr)
                                    ->select('item', 'descr', 'qty', 'precio',
                                        DB::raw('qty * precio as subtotal'))
                                    ->get()
                                    ->map(fn ($r) => (array) $r)
                                    ->toArray();
                            }),
                    ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHistorialVentas::route('/'),
            'view'  => Pages\ViewHistorialVenta::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        // Super admin tiene acceso
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->accesos['historial_ventas'] ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }
}
