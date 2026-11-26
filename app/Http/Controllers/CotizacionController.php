<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\PedidoServicio;
use App\Models\Servicio;
use App\Services\CotizacionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CotizacionController extends Controller
{
    public function index()
    {
        return view('cotizaciones.index', ['cotizaciones' => Cotizacion::with('pedido.cliente')->latest('fecha_emision')->paginate(15)]);
    }

    public function create(Request $r)
    {
        $r->validate(['pedido' => 'nullable|integer|exists:pedidos_servicio,id']);

        return view('cotizaciones.create', ['pedidos' => PedidoServicio::with('cliente')->orderByDesc('id')->get(), 'servicios' => Servicio::where('activo', true)->orderBy('nombre')->get(), 'seleccionado' => $r->integer('pedido')]);
    }

    public function store(Request $r, CotizacionService $service)
    {
        $data = $r->validate(['pedido_id' => 'required|integer|exists:pedidos_servicio,id', 'observaciones' => 'nullable|string|max:2000', 'items' => 'required|array|min:1|max:20', 'items.*.servicio_id' => 'required|integer|distinct|exists:servicios,id', 'items.*.cantidad' => 'required|integer|min:1|max:1000']);
        $q = $service->emitir($data, $r->user());

        return redirect()->route('cotizaciones.show', $q)->with('ok', 'Cotización emitida correctamente.');
    }

    public function show(Cotizacion $cotizacion)
    {
        $cotizacion->load(['pedido.cliente', 'pedido.equipo', 'detalles', 'emisor']);

        return view('cotizaciones.show', compact('cotizacion'));
    }

    public function pdf(Cotizacion $cotizacion)
    {
        $cotizacion->load(['pedido.cliente', 'pedido.equipo', 'detalles', 'emisor']);

        return Pdf::loadView('pdf.cotizacion', compact('cotizacion'))->setPaper('a4')->download('cotizacion-'.$cotizacion->id.'.pdf');
    }
}
