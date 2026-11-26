<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    protected $table = 'perfiles';

    protected $guarded = ['id'];

    public $timestamps = false;

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'perfil_id');
    }
}
