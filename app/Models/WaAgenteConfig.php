<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaAgenteConfig extends Model
{
    protected $table = 'wa_agente_config';

    protected $fillable = [
        'empresa_id', 'activo', 'modo', 'agente_nombre', 'agente_avatar', 'depositos', 'mensaje_bienvenida', 'horarios', 'ubicacion',
        'entrega', 'pago_efectivo', 'pago_transferencia', 'datos_transferencia', 'tono',
        'instrucciones', 'regla_mayorista', 'mayorista_activo', 'mayorista_min_unidades', 'cotizacion_pie', 'max_productos', 'max_fotos',
    ];

    protected $casts = [
        'activo'             => 'boolean',
        'depositos'          => 'array',
        'pago_efectivo'      => 'boolean',
        'mayorista_activo'   => 'boolean',
        'pago_transferencia' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
