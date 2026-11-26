<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCotizacion extends Model
{
    protected $table = 'detalles_cotizacion';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = ['precio_unitario' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}
