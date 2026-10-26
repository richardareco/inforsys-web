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

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
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
                        ->schema(array_merge(
                            [
                                Section::make('Cómo funcionan los accesos')
                                    ->description('Cada casilla habilita un módulo: aparece en el menú y se puede abrir. Lo que no esté marcado queda oculto y bloqueado para este usuario. El superadministrador siempre tiene todo; Usuarios y Empresas son solo del superadministrador.')
                                    ->schema([])->collapsible()->collapsed(),
                            ],
                            collect(\App\Support\Modulos::porGrupo())->map(
                                fn (array $modulos, string $grupo) => Section::make($grupo)
                                    ->schema(collect($modulos)->map(
                                        fn (array $m, string $clave) => Checkbox::make('accesos.' . $clave)
                                            ->label($m[0])
                                            ->helperText($m[2])
                                            ->default(in_array($clave, \App\Support\Modulos::sugeridos('vendedor'), true))
                                    )->values()->all())
                                    ->columns(2)
                            )->values()->all()
                        )),
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
