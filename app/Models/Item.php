<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $connection = 'delphi';
    protected $table     = 'items';
    protected $primaryKey = 'item';
    public    $incrementing = false;
    protected $keyType    = 'string';
    public    $timestamps  = false;

    protected $fillable = [
        'item',
        'scode',
        'scode1',
        'descr',
        'costo',
        'acosto',
        'precio1',
        'precio2',
        'precio3',
        'grupnr',
    ];

    public function images()
    {
        return $this->hasMany(ItemImage::class, 'item', 'item');
    }

    public function depos()
    {
        return $this->hasMany(ItemDepo::class, 'item', 'item');
    }

    public function getTotalStockAttribute()
    {
        return $this->depos()->sum('xqty') ?? 0;
    }
}
