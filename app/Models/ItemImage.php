<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemImage extends Model
{
    protected $connection = 'delphi';
    protected $table = 'itemsimage';
    protected $primaryKey = 'nro';
    public $timestamps = false;

    protected $fillable = ['item', 'image'];
}
