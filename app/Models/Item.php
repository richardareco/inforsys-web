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
}
