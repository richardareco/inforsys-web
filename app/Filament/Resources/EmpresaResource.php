<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpresaResource\Pages;
use App\Models\Empresa;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EmpresaResource extends Resource
{
    protected static ?string $model            = Empresa::class;
    protected static ?string $navigationIcon   = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel  = 'Empresas';
    protected static ?string $modelLabel       = 'Empresa';
    protected static ?string $pluralModelLabel = 'Empresas';
    protected static ?string $navigationGroup  = 'Administración';
    protected static ?int    $navigationSort   = 5;

    public static function canAccess(): bool
    {
        return Auth::check() && Auth::user()->isSuperAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('nombre')
                ->label('Nombre de la empresa')
                ->required()
                ->maxLength(255)
                ->columnSpan(2),

            TextInput::make('db_name')
                ->label('Base de datos (Delphi)')
                ->required()
                ->maxLength(100)
                ->helperText('Nombre exacto de la base de datos en el servidor Delphi. Ej: db, db_duarte, db_aty')
                ->columnSpan(2),

            FileUpload::make('logo')
                ->label('Logo')
                ->image()
                ->disk('public')
                ->directory('logos')
                ->maxSize(2048)
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'])
                ->helperText('PNG, JPG, SVG o WebP. Máx 2 MB. Se mostrará en el encabezado del panel.')
                ->columnSpan(2),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk('public')
                    ->height(36)
                    ->width(80)
                    ->defaultImageUrl(null),

                TextColumn::make('nombre')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('db_name')
                    ->label('Base de datos')
                    ->badge()
                    ->color('info'),

                TextColumn::make('users_count')
                    ->label('Usuarios')
                    ->counts('users')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('nombre');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEmpresas::route('/'),
            'create' => Pages\CreateEmpresa::route('/create'),
            'edit'   => Pages\EditEmpresa::route('/{record}/edit'),
        ];
    }
}
