<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaMensaje extends Model
{
    protected $table = 'wa_mensajes';

    protected $fillable = [
        'conversacion_id', 'direccion', 'autor', 'tipo', 'texto', 'media_url',
        'kapso_message_id', 'estado', 'error',
    ];
}
