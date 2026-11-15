<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Models\Item;
use App\Models\Grupo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\CheckUserAcceso;

class ItemResource extends Resource
{
    use CheckUserAcceso;
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Productos';

    protected static ?string $modelLabel = 'Producto';

    protected static ?string $pluralModelLabel = 'Productos';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Tabs::make('Producto')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                \Filament\Forms\Components\Tabs\Tab::make('Datos del producto')
                    ->icon('heroicon-o-cube')
                    ->schema([
                \Filament\Forms\Components\Section::make('Información Básica')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('item')
                            ->label('Código Producto')
                            ->disabled()
                            ->helperText('Se genera automáticamente'),

                        \Filament\Forms\Components\TextInput::make('scode')
                            ->label('Referencia')
                            ->maxLength(20),

                        \Filament\Forms\Components\TextInput::make('scode1')
                            ->label('Código de Barra')
                            ->maxLength(20),

                        \Filament\Forms\Components\TextInput::make('descr')
                            ->label('Descripción')
                            ->required()
                            ->maxLength(100),

                        \Filament\Forms\Components\Select::make('grupnr')
                            ->label('Categoría')
                            ->options(
                                Grupo::all()->pluck('descr', 'grupnr')->toArray()
                            )
                            ->searchable()
                            ->nullable(),
                    ])->columns(2),

                \Filament\Forms\Components\Section::make('Costos y Precios')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('costo')
                            ->label('Costo')
                            ->numeric()
                            ->step(0.0001),

                        \Filament\Forms\Components\TextInput::make('acosto')
                            ->label('Costo Actual')
                            ->numeric()
                            ->step(0.0001),

                        \Filament\Forms\Components\TextInput::make('precio1')
                            ->label('Precio 1')
                            ->numeric()
                            ->step(0.0001),

                        \Filament\Forms\Components\TextInput::make('precio2')
                            ->label('Precio 2')
                            ->numeric()
                            ->step(0.0001),

                        \Filament\Forms\Components\TextInput::make('precio3')
                            ->label('Precio 3')
                            ->numeric()
                            ->step(0.0001),
                    ])->columns(3),

                \Filament\Forms\Components\Section::make('Fotos del producto')
                    ->description('La primera foto es la principal (la que se ve en la lista y en las tarjetas). Hasta 10 fotos por producto.')
                    ->schema([
                        \Filament\Forms\Components\ViewField::make('fotos_actuales')
                            ->view('filament.resources.item-fotos')
                            ->dehydrated(false)
                            ->visible(fn (?\Illuminate\Database\Eloquent\Model $record) => $record !== null)
                            ->columnSpanFull(),
                        \Filament\Forms\Components\FileUpload::make('fotos_nuevas')
                            ->label('Agregar fotos')
                            ->image()
                            ->multiple()
                            ->maxFiles(\App\Services\Productos\FotosProducto::MAXIMO)
                            ->maxSize(5120)
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1', '4:3', '16:9'])
                            ->panelLayout('grid')
                            ->storeFiles(false) // se guardan en la base (itemsimage) al guardar el producto, no en el disco
                            ->helperText('Podés elegir varias a la vez. Cada foto se achica a 600 px para que cargue rápido.')
                            ->columnSpanFull(),
                    ])->columns(1),
                    ]),
                ...self::tabFicha(),
                    ]),
            ]);
    }

    /**
     * Pestaña "Información del producto": lo que se le muestra a la clienta en el POS de catálogo.
     * Incluye la ficha comercial (tabla Delphi items_ficha, opcional por base: sin la tabla no se arma, porque un campo
     * oculto igual intentaría leer la relación y fallaría) y las secciones extra que cada empresa quiera agregar.
     */
    private static function tabFicha(): array
    {
        $ficha = [];
        if (\App\Services\Productos\FichaProducto::tablaExiste()) {
            $ficha[] = \Filament\Forms\Components\Group::make()
                ->relationship('ficha', condition: fn (?array $state) => collect($state ?? [])->except(['destacado'])->filter(fn ($v) => $v !== null && $v !== '' && $v !== [])->isNotEmpty() || ($state['destacado'] ?? false))
                ->schema([
                    \Filament\Forms\Components\Section::make('Ficha del producto')
                        ->description('Esta información se le muestra a la clienta al tocar "Información" en el punto de venta de catálogo. Podés dejar vacío lo que no aplique.')
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('marca')->label('Marca')->maxLength(80),
                            \Filament\Forms\Components\TextInput::make('contenido')->label('Contenido')->maxLength(40)->placeholder('Ej: 30 ml, 250 g'),
                            \Filament\Forms\Components\TextInput::make('tipo_piel')->label('Tipo de piel')->maxLength(100)->placeholder('Ej: Mixta a grasa'),
                            \Filament\Forms\Components\Toggle::make('destacado')->label('Producto destacado')->inline(false),
                            \Filament\Forms\Components\Textarea::make('descripcion')->label('Descripción')->rows(3)->columnSpanFull(),
                            \Filament\Forms\Components\Textarea::make('beneficios')->label('Beneficios')->rows(3)->columnSpanFull(),
                            \Filament\Forms\Components\Textarea::make('modo_uso')->label('Modo de uso')->rows(3)->columnSpanFull(),
                            \Filament\Forms\Components\Textarea::make('ingredientes')->label('Ingredientes')->rows(3)->columnSpanFull(),
                            \Filament\Forms\Components\Textarea::make('advertencias')->label('Advertencias')->rows(2)->columnSpanFull(),
                            \Filament\Forms\Components\Repeater::make('tonos')
                                ->label('Tonos / colores')
                                ->schema([
                                    \Filament\Forms\Components\TextInput::make('nombre')->label('Nombre del tono')->required()->maxLength(40),
                                    \Filament\Forms\Components\ColorPicker::make('color')->label('Color')->required(),
                                ])
                                ->columns(2)
                                ->addActionLabel('Agregar tono')
                                ->maxItems(40)
                                ->defaultItems(0)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['nombre'] ?? null)
                                ->columnSpanFull(),
                        ])->columns(2),
                ]);
        }

        return [
            \Filament\Forms\Components\Tabs\Tab::make('Información del producto')
                ->icon('heroicon-o-document-text')
                ->schema([
                    ...$ficha,
                    \Filament\Forms\Components\Group::make()
                        ->relationship('infoExtra', condition: fn (?array $state) => \App\Models\ProductoInfoExtra::limpiar($state['secciones'] ?? []) !== [])
                        ->schema([
                            \Filament\Forms\Components\Section::make('Más información')
                                ->description('Agregá las secciones que quieras con el título que prefieras (por ejemplo "Garantía", "Cuidados" u "Origen"). Se muestran en la ficha del producto, como los tonos.')
                                ->schema([
                                    \Filament\Forms\Components\Repeater::make('secciones')
                                        ->label('Secciones')
                                        ->schema([
                                            \Filament\Forms\Components\TextInput::make('titulo')->label('Título')->required()->maxLength(\App\Models\ProductoInfoExtra::LARGO_TITULO)->placeholder('Ej: Garantía'),
                                            \Filament\Forms\Components\Textarea::make('contenido')->label('Contenido')->required()->rows(3)->maxLength(\App\Models\ProductoInfoExtra::LARGO_CONTENIDO)->placeholder('Ej: 6 meses por defectos de fábrica'),
                                        ])
                                        ->addActionLabel('Agregar información')
                                        ->maxItems(\App\Models\ProductoInfoExtra::MAXIMO)
                                        ->defaultItems(0)
                                        ->reorderable()
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['titulo'] ?? null),
                                ]),
                        ]),
                ]),
        ];
    }

    public static function table(Table $table): Table
    {
        $precio = function ($state) {
            $decimales = auth()->user()?->empresa?->decimales_precios ?? 2;
            return number_format((float) $state, $decimales, ',', '.');
        };

        return $table
            // Marca si el producto tiene foto, sin traer la imagen en cada fila
            ->modifyQueryUsing(fn (Builder $query) => $query->selectRaw(
                'items.*, EXISTS(SELECT 1 FROM itemsimage WHERE itemsimage.item = items.item) AS tiene_imagen'
            )->whereRaw("TRIM(COALESCE(items.item, '')) <> ''")) // filas sin código (basura en la base) no se listan
            ->columns([
                ImageColumn::make('foto')
                    ->label('')
                    ->getStateUsing(fn ($record) => $record->tiene_imagen
                        ? route('item-image.show', ['item' => $record->item])
                        : null)
                    ->defaultImageUrl('data:image/svg+xml;utf8,' . rawurlencode(
                        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40"><rect width="40" height="40" rx="8" fill="#f1f5f9"/><path d="M12 26l5-5 4 4 3-3 4 4v2a1 1 0 01-1 1H13a1 1 0 01-1-1v-2zm4-9a2 2 0 110 4 2 2 0 010-4z" fill="#cbd5e1"/></svg>'
                    ))
                    ->size(40)
                    ->extraImgAttributes(['loading' => 'lazy', 'class' => 'rounded-lg object-cover']),

                TextColumn::make('descr')
                    ->label('Producto')
                    ->description(fn ($record) => 'Cód. ' . $record->item)
                    ->size('sm')
                    ->weight('medium')
                    ->limit(42)
                    ->tooltip(fn ($record) => $record->descr)
                    ->searchable(query: fn (Builder $query, string $search) => $query->where(function ($q) use ($search) {
                        $q->where('items.descr', 'like', "%{$search}%")
                          ->orWhere('items.item', 'like', "%{$search}%")
                          ->orWhere('items.scode', 'like', "%{$search}%")
                          ->orWhere('items.scode1', 'like', "%{$search}%");
                    }))
                    ->sortable(),

                TextColumn::make('scode')
                    ->label('Referencia')
                    ->size('xs')
                    ->color('gray')
                    ->placeholder('—')
                    ->copyable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('scode1')
                    ->label('Cód. barra')
                    ->size('xs')
                    ->color('gray')
                    ->placeholder('—')
                    ->copyable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_stock')
                    ->label('Stock')
                    ->getStateUsing(fn($record) => (int)($record->depos()->sum('qty') ?? 0))
                    ->formatStateUsing(function($state) {
                        $empresa = auth()->user()?->empresa;
                        $decimales = $empresa?->decimales_cantidad ?? 0;
                        return number_format($state, $decimales, ',', '.');
                    })
                    ->size('sm')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('costo')
                    ->label('Costo')
                    ->formatStateUsing($precio)
                    ->size('sm')
                    ->color('warning')
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('precio1')
                    ->label('Precio 1')
                    ->formatStateUsing($precio)
                    ->size('sm')
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('precio2')
                    ->label('Precio 2')
                    ->formatStateUsing($precio)
                    ->size('sm')
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('precio3')
                    ->label('Precio 3')
                    ->formatStateUsing($precio)
                    ->size('sm')
                    ->weight('bold')
                    ->alignEnd()
                    ->sortable(),
            ])
            ->striped()
            ->recordClasses('pt-compacto')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->button()
                    ->action(function ($record) {
                        // La acción se maneja en el modal
                    })
                    ->modalContent(function ($record) {
                        return view('filament.resources.item-view-modal', ['record' => $record]);
                    })
                    ->modalHeading(fn($record) => $record->descr)
                    ->modalSubheading(fn($record) => "Código: {$record->item}")
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Guardar')
                    ->modalCancelActionLabel('Cancelar'),

                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-m-pencil-square')
                    ->button(),
            ], position: Tables\Enums\ActionsPosition::BeforeCells)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
        ];
    }

    public static function getCreateLabel(): string
    {
        return 'Crear Producto';
    }

    public static function getDeleteBulkActionLabel(): string
    {
        return 'Eliminar seleccionados';
    }

    public static function canAccess(): bool
    {
        return self::userHasAcceso('productos');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }

    // Nota: create y edit se manejan manualmente en ListItems sin registrar aquí
    // para evitar conflicto de compilación de rutas dinámicas en Filament v3
}
