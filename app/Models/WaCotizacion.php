<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WaCotizacion extends Model
{
    protected $table = 'wa_cotizaciones';

    protected $fillable = ['empresa_id', 'conversacion_id', 'contacto_id', 'estado', 'total'];

    protected $casts = ['total' => 'float'];

    public function getNumeroAttribute(): string
    {
        return 'COT-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WaCotizacionItem::class, 'cotizacion_id');
    }

    public function contacto(): BelongsTo
    {
        return $this->belongsTo(WaContacto::class, 'contacto_id');
    }

    public function conversacion(): BelongsTo
    {
        return $this->belongsTo(WaConversacion::class, 'conversacion_id');
    }

    public function pedido(): HasOne
    {
        return $this->hasOne(WaPedido::class, 'cotizacion_id');
    }
}
