<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';

    protected $guarded = ['id'];

    protected $hidden = ['password_hash'];

    protected $casts = ['activo' => 'boolean'];

    public function getAuthPasswordName()
    {
        return 'password_hash';
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'perfil_id');
    }

    public function es(string ...$roles): bool
    {
        return in_array($this->perfil->codigo, $roles, true);
    }
}
