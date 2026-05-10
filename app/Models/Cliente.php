<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $connection = 'delphi';
    protected $table = 'cliente';
    protected $primaryKey = 'nro';

    public $timestamps = false;

    protected $fillable = [
        'custnr',
        'custname',
        'shortname',
        'contacto',
        'ruc',
        'ruc_dv',
        'identidad',
        'telef1',
        'telef2',
        'celular1',
        'celular2',
        'addr',
        'ciudad',
        'pais',
        'email',
        'activo',
        'tipo_cliente',
        'plazo_credito',
        'limite_credito',
        'defprecio',
        'deuda',
        'credito',
        'saldo',
    ];

    protected $casts = [
        'activo'          => 'boolean',
        'fecha_nacimiento' => 'date',
        'fecha'           => 'date',
        'fecha_ultmov'    => 'date',
        'mod_fecha'       => 'datetime',
        'limite_credito'  => 'decimal:4',
        'deuda'           => 'decimal:4',
        'credito'         => 'decimal:4',
        'saldo'           => 'decimal:4',
    ];
}
