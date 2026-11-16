<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    protected $fillable = ['empresa_id', 'mes', 'personal_id', 'monto'];

    protected $casts = [
        'mes'   => 'date',
        'monto' => 'decimal:2',
    ];

    /** Metas de la empresa (sin vendedor), como hasta ahora. */
    public function scopeDeLaEmpresa($q)
    {
        return $q->where('personal_id', '');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
