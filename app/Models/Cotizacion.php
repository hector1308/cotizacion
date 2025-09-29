<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';

    protected $fillable = [
        'tipo_dolar',
        'tipo_valor',
        'valor',
        'fecha',
    ];

    public $timestamps = false;
}


