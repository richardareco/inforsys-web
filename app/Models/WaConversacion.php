<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaConversacion extends Model
{
    protected $table = 'wa_conversaciones';

    protected $fillable = ['empresa_id', 'linea_id', 'contacto_id', 'kapso_conversation_id', 'estado', 'ultimo_mensaje_at', 'carrito', 'carrito_at'];

    protected $casts = ['ultimo_mensaje_at' => 'datetime', 'carrito' => 'array', 'carrito_at' => 'datetime'];

    public function linea(): BelongsTo
    {
        return $this->belongsTo(WaLinea::class, 'linea_id');
    }

    public function contacto(): BelongsTo
    {
        return $this->belongsTo(WaContacto::class, 'contacto_id');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(WaMensaje::class, 'conversacion_id');
    }
}
