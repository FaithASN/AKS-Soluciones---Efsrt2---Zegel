<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialEstado extends Model
{
    protected $table = 'historial_estados';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = ['fecha_cambio' => 'datetime'];

    public function anterior()
    {
        return $this->belongsTo(EstadoServicio::class, 'estado_anterior_id');
    }

    public function nuevo()
    {
        return $this->belongsTo(EstadoServicio::class, 'estado_nuevo_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
