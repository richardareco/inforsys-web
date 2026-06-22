<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresupuestoResource\Pages;
use App\Models\Presupuesto;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PresupuestoResource extends Resource
{
    protected static ?string $model            = Presupuesto::class;
    protected static ?string $navigationIcon   = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel  = 'Presupuestos';
    protected static ?string $modelLabel       = 'Presupuesto';
    protected static ?string $pluralModelLabel = 'Presupuestos';
    protected static ?string $navigationGroup  = 'Ventas';
    protected static ?int    $navigationSort   = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select(
                'nro', 'presnr', 'fecha', 'custnr', 'total', 'dscto',
                'obs', 'anulado', 'cerrado', 'enviado', 'pernr',
                DB::connection('delphi')->raw(
                    '(SELECT custname FROM cliente WHERE cliente.custnr = presupuesto1.custnr LIMIT 1) as custname'
                ),
                DB::connection('delphi')->raw(
                    '(SELECT celular1 FROM cliente WHERE cliente.custnr = presupuesto1.custnr LIMIT 1) as celular'
                )
            )
            ->orderByDesc('fecha')
            ->orderByDesc('nro');
    }

    public static function form(Form $form): Form
    {
        $clientes = DB::connection('delphi')
            ->table('cliente')->select('custnr', 'custname')
            ->orderBy('custname')->limit(500)->get()
            ->mapWithKeys(fn ($c) => [$c->custnr => "{$c->custname} ({$c->custnr})"])
            ->toArray();

        $depositos = DB::connection('delphi')
            ->table('deposito')->select('deponr', 'depo_nombre')
            ->orderBy('deponr')->get()
            ->mapWithKeys(fn ($d) => [$d->deponr => $d->depo_nombre])
            ->toArray();

        return $form->schema([
            Select::make('custnr')
                ->label('Cliente')
                ->options($clientes)
                ->searchable()
                ->required()
                ->columnSpan(2),

            Select::make('depo')
                ->label('Depósito')
                ->options($depositos)
                ->required(),

            TextInput::make('pdscto')
                ->label('Descuento %')
                ->numeric()
                ->default(0)
                ->suffix('%'),

            Textarea::make('obs')
                ->label('Observaciones')
                ->columnSpanFull()
                ->rows(2),

            Repeater::make('items')
                ->label('Ítems')
                ->columnSpanFull()
                ->schema([
                    Select::make('item')
                        ->label('Producto')
                        ->searchable()
                        ->required()
                        ->columnSpan(2)
                        ->getSearchResultsUsing(fn (string $search) =>
                            DB::connection('delphi')->table('items')
                                ->where(fn ($q) => $q
                                    ->where('descr', 'like', "%{$search}%")
                                    ->orWhere('scode', 'like', "%{$search}%")
                                    ->orWhere('item', 'like', "%{$search}%"))
                                ->limit(15)
                                ->pluck('descr', 'item')
                                ->toArray()
                        )
                        ->getOptionLabelUsing(fn ($value) =>
                            DB::connection('delphi')->table('items')->where('item', $value)->value('descr') ?? $value
                        )
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (!$state) return;
                            $item = DB::connection('delphi')->table('items')->where('item', $state)->first();
                            if ($item) {
                                $set('descr',  $item->descr);
                                $set('precio', $item->precio3);
                                $set('costo',  $item->costo);
                            }
                        }),

                    TextInput::make('descr')
                        ->label('Descripción')
                        ->required()
                        ->columnSpan(2),

                    TextInput::make('qty')
                        ->label('Cantidad')
                        ->numeric()
                        ->default(1)
                        ->required(),

                    TextInput::make('precio')
                        ->label('Precio Unit.')
                        ->numeric()
                        ->required(),

                    TextInput::make('costo')
                        ->label('Costo')
                        ->numeric()
                        ->default(0)
                        ->hidden(),
                ])
                ->columns(6)
                ->addActionLabel('+ Agregar ítem')
                ->reorderable()
                ->collapsible(),
        ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('presnr')
                    ->label('Nro.')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('custname')
                    ->label('Cliente')
                    ->searchable(query: fn (Builder $q, string $s) =>
                        $q->where(DB::connection('delphi')->raw(
                            '(SELECT custname FROM cliente WHERE cliente.custnr = presupuesto1.custnr LIMIT 1)'
                        ), 'like', "%{$s}%")
                    )
                    ->limit(30),

                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Gs. ' . number_format($state, 0, ',', '.'))
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->anulado) return 'anulado';
                        if ($record->cerrado) return 'convertido';
                        return 'activo';
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'anulado'    => 'Anulado',
                        'convertido' => 'Convertido',
                        default      => 'Activo',
                    })
                    ->color(fn ($state) => match($state) {
                        'anulado'    => 'danger',
                        'convertido' => 'success',
                        default      => 'info',
                    }),

                TextColumn::make('obs')
                    ->label('Observación')
                    ->limit(30)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('fecha')
                    ->form([
                        DatePicker::make('desde')->label('Desde')->native(false),
                        DatePicker::make('hasta')->label('Hasta')->native(false),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['desde']) $query->whereDate('fecha', '>=', $data['desde']);
                        if ($data['hasta']) $query->whereDate('fecha', '<=', $data['hasta']);
                    }),

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(['activo' => 'Activo', 'convertido' => 'Convertido', 'anulado' => 'Anulado'])
                    ->query(function (Builder $query, array $data) {
                        if (!$data['value']) return;
                        if ($data['value'] === 'activo')     $query->where('anulado', 0)->where('cerrado', 0);
                        if ($data['value'] === 'convertido') $query->where('cerrado', 1);
                        if ($data['value'] === 'anulado')    $query->where('anulado', 1);
                    }),
            ])
            ->actions([
                ViewAction::make()->label('Ver'),
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn ($record) => route('presupuesto.pdf', $record->presnr))
                    ->openUrlInNewTab(),
                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->url(function ($record) {
                        $phone   = preg_replace('/\D/', '', $record->celular ?? '');
                        $mensaje = "Hola {$record->custname}, le enviamos el presupuesto Nro. {$record->presnr} por Gs. " . number_format($record->total, 0, ',', '.') . ". Puede verlo en: " . route('presupuesto.pdf', $record->presnr);
                        return 'https://wa.me/' . $phone . '?text=' . urlencode($mensaje);
                    })
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->celular)),
            ])
            ->defaultSort('fecha', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPresupuestos::route('/'),
            'create' => Pages\CreatePresupuesto::route('/create'),
            'view'   => Pages\ViewPresupuesto::route('/{record}'),
        ];
    }
}
