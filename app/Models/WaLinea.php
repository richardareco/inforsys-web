<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaLinea extends Model
{
    protected $table = 'wa_lineas';

    protected $fillable = ['empresa_id', 'phone_number_id', 'nombre', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
