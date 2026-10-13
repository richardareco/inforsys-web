<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemDepo extends Model
{
    protected $connection = 'delphi';
    protected $table = 'itemsdepo';
    protected $primaryKey = 'nro';
    public $timestamps = false;

    protected $fillable = [
        'depo',
        'item',
        'qty',
        'xqty',
    ];
}
