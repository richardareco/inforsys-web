<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MetaResource\Pages;
use App\Models\Meta;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MetaResource extends Resource
{
    protected static ?string $model             = Meta::class;
    protected static ?string $navigationIcon    = 'heroicon-o-flag';
    protected static ?string $navigationLabel   = 'Metas de Ventas';
    protected static ?string $modelLabel        = 'Meta';
    protected static ?string $pluralModelLabel  = 'Metas de Ventas';
    protected static ?string $navigationGroup   = 'Configuración';
    protected static ?int    $navigationSort    = 1;

    public static function canAccess(): bool
    {
        return \App\Support\Modulos::permite(Auth::user(), 'metas');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }

    public static function form(Form $form): Form
    {
        $meses = [];
        $start = now()->startOfMonth()->subMonths(3);
        for ($i = 0; $i < 18; $i++) {
            $date = $start->copy()->addMonths($i);
            $meses[$date->format('Y-m-d')] = $date->isoFormat('MMMM YYYY');
        }

        return $form->schema([
            Select::make('personal_id')
                ->label('Vendedor')
                ->options(fn () => ['' => 'Meta de toda la empresa'] + self::vendedores())
                ->default('')
                ->native(false)
                ->searchable()
                ->helperText('Elegí un vendedor para fijarle una meta propia; se ve en su dashboard.'),

            Select::make('mes')
                ->label('Mes')
                ->options($meses)
                ->default(now()->startOfMonth()->format('Y-m-d'))
                ->required()
                ->native(false),

            TextInput::make('monto')
                ->label('Meta de Ventas (Gs.)')
                ->required()
                ->rules(['numeric', 'min:0'])
                ->placeholder('Ej: 5000000'),
        ]);
    }

    public static function table(Table $table): Table
    {
        $isSuperAdmin = Auth::user()?->isSuperAdmin();

        return $table
            ->columns([
                TextColumn::make('mes')
                    ->label('Mes')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->isoFormat('MMMM YYYY'))
                    ->sortable(),

                TextColumn::make('personal_id')
                    ->label('Vendedor')
                    ->formatStateUsing(fn ($state) => $state === '' || $state === null ? 'Toda la empresa' : (self::vendedores()[$state] ?? $state))
                    ->badge()
                    ->color(fn ($state) => $state === '' || $state === null ? 'gray' : 'info'),

                TextColumn::make('monto')
                    ->label('Meta (Gs.)')
                    ->formatStateUsing(fn ($state) => 'Gs. ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->visible($isSuperAdmin)
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('mes', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /** Vendedores de la base Delphi de la empresa (pernr => nombre). Si la base no responde, lista vacía. */
    public static function vendedores(): array
    {
        static $cache = [];
        $base = (string) config('database.connections.delphi.database');

        return $cache[$base] ??= (function () {
            try {
                return \Illuminate\Support\Facades\DB::connection('delphi')->table('personal')->orderBy('pername')
                    ->pluck('pername', 'pernr')->map(fn ($n) => trim((string) $n))->all();
            } catch (\Throwable) {
                return [];
            }
        })();
    }

    /** ¿Ya hay una meta para ese mes y ese vendedor (o toda la empresa)? Evita duplicados antes de llegar a la base. */
    public static function existeMeta(int $empresaId, string $mes, string $personal, ?int $ignorar = null): bool
    {
        return Meta::where('empresa_id', $empresaId)->whereDate('mes', $mes)->where('personal_id', $personal)
            ->when($ignorar, fn ($q) => $q->where('id', '!=', $ignorar))->exists();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (! Auth::user()?->isSuperAdmin()) {
            $query->where('empresa_id', Auth::user()->empresa_id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMetas::route('/'),
            'create' => Pages\CreateMeta::route('/create'),
            'edit'   => Pages\EditMeta::route('/{record}/edit'),
        ];
    }
}
