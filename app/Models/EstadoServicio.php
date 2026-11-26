<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoServicio extends Model
{
    protected $table = 'estados_servicio';

    protected $guarded = ['id'];

    public $timestamps = false;
}
