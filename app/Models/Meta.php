<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    protected $fillable = ['empresa_id', 'mes', 'monto'];

    protected $casts = [
        'mes'   => 'date',
        'monto' => 'decimal:2',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
