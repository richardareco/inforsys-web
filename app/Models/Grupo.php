<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $connection = 'delphi';
    protected $table = 'grupo';
    protected $primaryKey = 'nro';
    public $timestamps = false;

    protected $fillable = ['grupnr', 'descr', 'subcode', 'grup_id', 'tipo_submenu', 'menu_btn'];
}
