<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaEvento extends Model
{
    protected $table = 'wa_eventos';

    protected $fillable = ['idempotency_key', 'evento', 'phone_number_id', 'estado', 'error', 'payload'];

    protected $casts = ['payload' => 'array'];
}
