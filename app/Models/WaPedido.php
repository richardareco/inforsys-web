<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaPedido extends Model
{
    protected $table = 'wa_pedidos';

    protected $fillable = [
        'empresa_id', 'conversacion_id', 'contacto_id', 'cotizacion_id', 'estado', 'origen', 'nombre_cliente', 'telefono',
        'entrega_tipo', 'direccion_entrega', 'forma_pago', 'requiere_factura', 'ruc', 'observacion', 'total',
        'preparado_mensaje_id', 'confirmado_at', 'venta_invnr', 'cliente_delphi', 'pagado_at', 'anulado_at', 'gestionado_por', 'gestionado_at', 'nota_interna',
    ];

    protected $casts = [
        'total'            => 'float',
        'requiere_factura' => 'boolean',
        'confirmado_at'    => 'datetime',
        'gestionado_at'    => 'datetime',
        'pagado_at'        => 'datetime',
        'anulado_at'       => 'datetime',
    ];

    public function getNumeroAttribute(): string
    {
        return 'PED-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WaPedidoItem::class, 'pedido_id');
    }

    public function contacto(): BelongsTo
    {
        return $this->belongsTo(WaContacto::class, 'contacto_id');
    }

    public function conversacion(): BelongsTo
    {
        return $this->belongsTo(WaConversacion::class, 'conversacion_id');
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(WaCotizacion::class, 'cotizacion_id');
    }

    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestionado_por');
    }
}
