<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoServicio extends Model
{
    protected $table = 'pedidos_servicio';

    protected $guarded = ['id'];

    const CREATED_AT = null;

    protected $casts = ['fecha_ingreso' => 'datetime', 'fecha_entrega' => 'datetime', 'fecha_cobro' => 'datetime', 'importe_cobrado' => 'decimal:2'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoServicio::class, 'estado_actual_id');
    }

    public function registrador()
    {
        return $this->belongsTo(Usuario::class, 'registrado_por');
    }

    public function historial()
    {
        return $this->hasMany(HistorialEstado::class, 'pedido_id')->orderBy('fecha_cambio')->orderBy('id');
    }

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'pedido_id')->latest('fecha_emision');
    }
}
