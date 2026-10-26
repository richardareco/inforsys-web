<?php

namespace App\Filament\Resources\WaPedidoResource\Pages;

use App\Filament\Resources\WaPedidoResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewWaPedido extends ViewRecord
{
    protected static string $resource = WaPedidoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...WaPedidoResource::accionesCabecera(),
            Action::make('conversacion')->label('Ver conversación')->icon('heroicon-o-chat-bubble-left-right')->color('gray')
                ->visible(fn () => WaPedidoResource::urlConversacion($this->record) !== null)
                ->url(fn () => WaPedidoResource::urlConversacion($this->record)),
            Action::make('volver')->label('Volver')->icon('heroicon-o-arrow-left')->color('gray')
                ->url(WaPedidoResource::getUrl('index')),
        ];
    }
}
