<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    protected $fillable = ['nombre', 'db_name', 'logo', 'tipo', 'modulo_caja_enabled', 'permitir_venta_sin_stock', 'mostrar_productos_sin_stock'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
