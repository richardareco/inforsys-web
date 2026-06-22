<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $connection = 'delphi';
    protected $table      = 'invo1';
    protected $primaryKey = 'nro';

    public $timestamps = false;

    protected $casts = [
        'fecha'    => 'date',
        'total'    => 'float',
        'saldo'    => 'float',
        'acredito' => 'float',
    ];
}
