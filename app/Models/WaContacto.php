<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaContacto extends Model
{
    protected $table = 'wa_contactos';

    protected $fillable = ['empresa_id', 'telefono', 'nombre', 'direccion', 'ruc'];
}
