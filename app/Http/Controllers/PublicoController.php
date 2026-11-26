<?php

namespace App\Http\Controllers;

use App\Models\PedidoServicio;
use App\Models\Servicio;
use Illuminate\Http\Request;

class PublicoController extends Controller
{
    public function inicio()
    {
        return view('publico.inicio', ['servicios' => Servicio::where('activo', true)->orderBy('id')->get()]);
    }

    public function servicios()
    {
        return view('publico.servicios', ['servicios' => Servicio::where('activo', true)->orderBy('id')->get()]);
    }

    public function seguimiento()
    {
        return response()->view('publico.seguimiento', ['pedido' => null, 'consultado' => false])->header('Cache-Control', 'no-store');
    }

    public function consultar(Request $r)
    {
        $data = $r->validate(['codigo' => 'required|string|regex:/^[a-fA-F0-9]{32}$/']);
        $pedido = PedidoServicio::with(['equipo', 'estado', 'historial.nuevo'])->where('codigo_seguimiento', strtolower($data['codigo']))->first();

        // La vista solo presenta estados y datos generales del equipo; no muestra cliente, teléfono, cobros ni observaciones internas.
        return response()->view('publico.seguimiento', ['pedido' => $pedido, 'consultado' => true, 'codigo' => $data['codigo']])->header('Cache-Control', 'no-store');
    }
}
