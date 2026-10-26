<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaPedidoItem extends Model
{
    protected $table = 'wa_pedido_items';

    public $timestamps = false;

    protected $fillable = ['pedido_id', 'item', 'descr', 'cantidad', 'precio', 'subtotal'];

    protected $casts = ['cantidad' => 'float', 'precio' => 'float', 'subtotal' => 'float'];
}
