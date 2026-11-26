<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';

    protected $guarded = ['id'];

    const CREATED_AT = null;

    protected $casts = ['fecha_emision' => 'datetime', 'total' => 'decimal:2'];

    public function pedido()
    {
        return $this->belongsTo(PedidoServicio::class, 'pedido_id');
    }

    public function emisor()
    {
        return $this->belongsTo(Usuario::class, 'emitida_por');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCotizacion::class, 'cotizacion_id');
    }
}
