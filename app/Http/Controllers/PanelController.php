<?php

namespace App\Http\Controllers;

use App\Models\EstadoServicio;
use App\Models\PedidoServicio;

class PanelController extends Controller
{
    public function __invoke()
    {
        return view('gestion.panel', ['estados' => EstadoServicio::orderBy('orden')->get(), 'conteos' => PedidoServicio::selectRaw('estado_actual_id, count(*) as total')->groupBy('estado_actual_id')->pluck('total', 'estado_actual_id'), 'recientes' => PedidoServicio::with(['cliente', 'equipo', 'estado'])->latest('fecha_ingreso')->limit(8)->get(), 'listos' => PedidoServicio::with(['cliente', 'equipo'])->where('estado_actual_id', 4)->limit(5)->get()]);
    }
}
