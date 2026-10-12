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
use Filament\Tables\Table;

class ItemResource extends Resource
{
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

                \Filament\Forms\Components\Section::make('Foto del Producto')
                    ->schema([
                        \Filament\Forms\Components\FileUpload::make('image')
                            ->label('Subir Foto')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['16:9', '4:3', '1:1'])
                            ->directory('products')
                            ->maxSize(5120)
                            ->dehydrated(false)
                            ->hiddenOn('index'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item')
                    ->label('Código')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('descr')
                    ->label('Descripción')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('precio1')
                    ->label('Precio 1')
                    ->sortable(),

                TextColumn::make('precio2')
                    ->label('Precio 2')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
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

    // Nota: create y edit se manejan manualmente en ListItems sin registrar aquí
    // para evitar conflicto de compilación de rutas dinámicas en Filament v3
}
