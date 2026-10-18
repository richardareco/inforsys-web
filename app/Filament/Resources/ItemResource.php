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
                            ->hiddenOn('index')
                            ->dehydrated(false)
                            ->saveUploadedFileUsing(function($file, $record) {
                                $path = $file->store('products', 'local');
                                $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($path);

                                // Comprimir y redimensionar imagen con GD
                                $content = \Illuminate\Support\Facades\Storage::disk('local')->get($path);
                                $originalSize = strlen($content);

                                try {
                                    $image = imagecreatefromstring($content);
                                    if ($image) {
                                        $width = imagesx($image);
                                        $height = imagesy($image);

                                        // Redimensionar a 600x600 píxeles (estandarizar tamaño)
                                        $standardSize = 600;
                                        $resized = imagecreatetruecolor($standardSize, $standardSize);

                                        // Crear fondo blanco para mantener proporción
                                        $white = imagecolorallocate($resized, 255, 255, 255);
                                        imagefill($resized, 0, 0, $white);

                                        // Calcular dimensiones para centrar imagen manteniendo proporción
                                        $ratio = min($standardSize / $width, $standardSize / $height);
                                        $newWidth = (int)($width * $ratio);
                                        $newHeight = (int)($height * $ratio);
                                        $offsetX = (int)(($standardSize - $newWidth) / 2);
                                        $offsetY = (int)(($standardSize - $newHeight) / 2);

                                        imagecopyresampled($resized, $image, $offsetX, $offsetY, 0, 0, $newWidth, $newHeight, $width, $height);

                                        ob_start();
                                        imagejpeg($resized, null, 75);
                                        $content = ob_get_clean();
                                        imagedestroy($resized);
                                        imagedestroy($image);

                                        $newSize = strlen($content);
                                        $reduction = round((1 - $newSize / $originalSize) * 100);
                                        \Illuminate\Support\Facades\Log::info("🖼️ Imagen comprimida: $originalSize bytes -> $newSize bytes (reducción: $reduction%)");
                                    }
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::warning("⚠️ Error comprimiendo: " . $e->getMessage());
                                }

                                if ($record) {
                                    // En EDIT: eliminar imagen anterior y crear nueva (fuerza actualización)
                                    \App\Models\ItemImage::where('item', $record->item)->delete();
                                    \App\Models\ItemImage::create([
                                        'item' => $record->item,
                                        'image' => $content
                                    ]);
                                    \Illuminate\Support\Facades\Log::info("✅ EDIT: Foto actualizada para item {$record->item}");
                                } else {
                                    // En CREATE: guardar temporalmente en cache
                                    $tempKey = 'pending_image_' . uniqid();
                                    \Illuminate\Support\Facades\Cache::put($tempKey, [
                                        'path' => $path,
                                        'content' => $content
                                    ], now()->addMinutes(30));
                                    session(['pending_image_key' => $tempKey]);
                                    \Illuminate\Support\Facades\Log::info("📤 CREATE: Foto en cache con key {$tempKey}");
                                }

                                return $path;
                            }),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('item')
                    ->label('Foto')
                    ->getStateUsing(function ($record) {
                        try {
                            $itemImage = \App\Models\ItemImage::where('item', $record->item)->first();
                            if ($itemImage && $itemImage->image) {
                                return 'data:image/jpeg;base64,' . base64_encode($itemImage->image);
                            }
                        } catch (\Exception $e) {
                            // Si hay error, retornar null
                        }
                        return null;
                    })
                    ->circular()
                    ->size(50)
                    ->fallbackIcon('heroicon-o-photo'),

                TextColumn::make('item')
                    ->label('Código')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('descr')
                    ->label('Descripción')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_stock')
                    ->label('Stock')
                    ->getStateUsing(fn($record) => (int)($record->depos()->sum('xqty') ?? 0))
                    ->formatStateUsing(fn($state) => number_format($state, 0))
                    ->sortable(),

                TextColumn::make('precio1')
                    ->label('Precio 1')
                    ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.'))
                    ->sortable(),

                TextColumn::make('precio2')
                    ->label('Precio 2')
                    ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.'))
                    ->sortable(),

                TextColumn::make('precio3')
                    ->label('Precio 3')
                    ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-m-eye')
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
                    ->label('Editar'),
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
