<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Empresa;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model            = User::class;
    protected static ?string $navigationIcon   = 'heroicon-o-users';
    protected static ?string $navigationLabel  = 'Usuarios';
    protected static ?string $modelLabel       = 'Usuario';
    protected static ?string $pluralModelLabel = 'Usuarios';
    protected static ?string $navigationGroup  = 'Administración';
    protected static ?int    $navigationSort   = 10;

    // Solo visible para superadmin
    public static function canAccess(): bool
    {
        return Auth::check() && Auth::user()->isSuperAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('Correo electrónico')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            TextInput::make('password')
                ->label('Contraseña')
                ->password()
                ->revealable()
                ->minLength(8)
                ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $operation) => $operation === 'create')
                ->helperText(fn (string $operation) => $operation === 'edit' ? 'Dejá vacío para no cambiarla' : null),

            Select::make('empresa_id')
                ->label('Empresa')
                ->relationship('empresa', 'nombre')
                ->options(Empresa::query()->pluck('nombre', 'id'))
                ->searchable()
                ->preload()
                ->required()
                ->helperText('Empresa a la que pertenece este usuario (define su base de datos)'),

            Select::make('role')
                ->label('Rol')
                ->required()
                ->options([
                    'superadmin'    => 'Super Admin',
                    'administrador' => 'Administrador',
                ])
                ->default('administrador')
                ->helperText('Super Admin puede ver y configurar todo. Administrador solo ve los datos.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('role')
                    ->label('Rol')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'superadmin'    => 'danger',
                        'administrador' => 'info',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'superadmin'    => 'Super Admin',
                        'administrador' => 'Administrador',
                        default         => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
