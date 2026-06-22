<?php

namespace App\Filament\Resources\PresupuestoResource\Pages;

use App\Filament\Resources\PresupuestoResource;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewPresupuesto extends ViewRecord
{
    protected static string $resource = PresupuestoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn () => route('presupuesto.pdf', $this->record->presnr))
                ->openUrlInNewTab(),

            Action::make('whatsapp')
                ->label('Enviar WhatsApp')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('success')
                ->url(function () {
                    $cliente = DB::connection('delphi')
                        ->table('cliente')
                        ->where('custnr', $this->record->custnr)
                        ->first();
                    $phone   = preg_replace('/\D/', '', $cliente?->celular1 ?? '');
                    $nombre  = $cliente?->custname ?? '';
                    $mensaje = "Hola {$nombre}, le enviamos el presupuesto Nro. {$this->record->presnr} por Gs. " . number_format($this->record->total, 0, ',', '.') . ". Puede verlo en: " . route('presupuesto.pdf', $this->record->presnr);
                    return 'https://wa.me/' . $phone . '?text=' . urlencode($mensaje);
                })
                ->openUrlInNewTab()
                ->visible(fn () => !empty(DB::connection('delphi')->table('cliente')->where('custnr', $this->record->custnr)->value('celular1'))),

            Action::make('volver')
                ->label('Volver')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(PresupuestoResource::getUrl('index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Datos del Presupuesto')
                ->columns(4)
                ->schema([
                    TextEntry::make('presnr')->label('Nro. Presupuesto')->weight('bold'),
                    TextEntry::make('fecha')->label('Fecha')->date('d/m/Y'),
                    TextEntry::make('custname')
                        ->label('Cliente')
                        ->getStateUsing(fn ($record) => DB::connection('delphi')->table('cliente')->where('custnr', $record->custnr)->value('custname') ?? $record->custnr),
                    TextEntry::make('custnr')->label('Cód. Cliente'),

                    TextEntry::make('total')
                        ->label('Total')
                        ->formatStateUsing(fn ($state) => 'Gs. ' . number_format($state, 0, ',', '.'))
                        ->weight('bold'),
                    TextEntry::make('dscto')
                        ->label('Descuento')
                        ->formatStateUsing(fn ($state) => 'Gs. ' . number_format($state, 0, ',', '.')),
                    TextEntry::make('obs')->label('Observaciones')->placeholder('—')->columnSpan(2),
                ]),

            Section::make('Ítems')
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
                        ->getStateUsing(fn ($record) =>
                            DB::connection('delphi')
                                ->table('presupuesto2')
                                ->where('presnr', $record->presnr)
                                ->where('anulado', 0)
                                ->select('item', 'descr', 'qty', 'precio', DB::raw('qty * precio as subtotal'))
                                ->get()
                                ->map(fn ($r) => (array) $r)
                                ->toArray()
                        ),
                ]),
        ]);
    }
}
