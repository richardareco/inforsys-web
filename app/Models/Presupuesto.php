<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    protected $connection = 'delphi';
    protected $table      = 'presupuesto1';
    protected $primaryKey = 'nro';

    public $timestamps = false;

    protected $fillable = [
        'presnr', 'custnr', 'pernr', 'depo', 'total', 'ctotal',
        'totalv', 'dscto', 'pdscto', 'obs', 'fecha', 'hora',
        'flag', 'anulado', 'cerrado', 'enviado', 'tipo_moneda',
    ];

    protected $casts = [
        'fecha'   => 'date',
        'total'   => 'float',
        'dscto'   => 'float',
        'ctotal'  => 'float',
        'anulado' => 'boolean',
        'cerrado' => 'boolean',
        'enviado' => 'boolean',
    ];
}
