<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaCotizacionItem extends Model
{
    protected $table = 'wa_cotizacion_items';

    public $timestamps = false;

    protected $fillable = ['cotizacion_id', 'item', 'descr', 'cantidad', 'precio', 'subtotal'];

    protected $casts = ['cantidad' => 'float', 'precio' => 'float', 'subtotal' => 'float'];
}
