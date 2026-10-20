<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Empresa;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\CheckUserAcceso;

class UserResource extends Resource
{
    use CheckUserAcceso;
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
            Tabs::make('Configuración del Usuario')
                ->tabs([
                    Tab::make('General')
                        ->schema([
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
                                    'cajero'        => 'Cajero',
                                    'vendedor'      => 'Vendedor',
                                ])
                                ->default('administrador')
                                ->helperText('Super Admin: acceso total. Administrador: gestión general. Cajero: cierre y pagos. Vendedor: solo ventas.'),
                        ]),

                    Tab::make('Configuración de Ventas')
                        ->schema([
                            Select::make('precio_minimo_permitido')
                                ->label('Nivel de precio mínimo permitido')
                                ->options([
                                    1 => 'Precio 1 (más bajo)',
                                    2 => 'Precio 2 (intermedio)',
                                    3 => 'Precio 3 (estándar - sin descuento)',
                                ])
                                ->default(3)
                                ->required()
                                ->helperText('Define hasta qué precio puede bajar al cliente. Ej: si elige Precio 2, no podrá usar Precio 1.'),

                            Select::make('personal_id')
                                ->label('Vendedor asignado')
                                ->options(function () {
                                    return DB::connection('delphi')
                                        ->table('personal')
                                        ->pluck('pername', 'pernr')
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->helperText('Vendedor que se asignará automáticamente cuando este usuario se loguee.'),
                        ]),

                    Tab::make('Accesos')
                        ->schema([
                            Section::make('Módulos Disponibles')
                                ->description('Selecciona qué módulos puede acceder este usuario')
                                ->schema([
                                    Checkbox::make('accesos.dashboard')
                                        ->label('Dashboard')
                                        ->default(true),

                                    Checkbox::make('accesos.ventas')
                                        ->label('Ventas (POS)')
                                        ->default(true),

                                    Checkbox::make('accesos.clientes')
                                        ->label('Clientes')
                                        ->default(true),

                                    Checkbox::make('accesos.productos')
                                        ->label('Productos / Ítems')
                                        ->default(true),

                                    Checkbox::make('accesos.historial_ventas')
                                        ->label('Historial de Ventas')
                                        ->default(false),

                                    Checkbox::make('accesos.cierre_caja')
                                        ->label('Cierre de Caja')
                                        ->default(false),

                                    Checkbox::make('accesos.cuentas_cobrar')
                                        ->label('Cuentas por Cobrar')
                                        ->default(false),

                                    Checkbox::make('accesos.proveedores')
                                        ->label('Proveedores')
                                        ->default(false),

                                    Checkbox::make('accesos.compras')
                                        ->label('Compras')
                                        ->default(false),

                                    Checkbox::make('accesos.gastos')
                                        ->label('Gastos')
                                        ->default(false),

                                    Checkbox::make('accesos.reportes')
                                        ->label('Informes')
                                        ->default(false),

                                    Checkbox::make('accesos.configuraciones')
                                        ->label('Configuraciones')
                                        ->default(false),

                                    Checkbox::make('accesos.usuarios')
                                        ->label('Usuarios')
                                        ->default(false),
                                ])->columns(2),
                        ]),
                ]),
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
