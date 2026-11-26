<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\EstadoServicio;
use App\Models\PedidoServicio;
use App\Services\PedidoService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PedidoController extends Controller
{
    public function index(Request $r)
    {
        $r->validate(['q' => 'nullable|string|max:100', 'estado' => 'nullable|integer|exists:estados_servicio,id', 'desde' => 'nullable|date_format:Y-m-d', 'hasta' => 'nullable|date_format:Y-m-d|after_or_equal:desde']);
        $query = PedidoServicio::with(['cliente', 'equipo', 'estado']);
        if ($q = $r->string('q')->trim()->value()) {
            $query->where(function ($b) use ($q) {
                $b->where('codigo_seguimiento', 'like', "%$q%")->orWhereHas('cliente', fn ($c) => $c->where('nombres', 'like', "%$q%"))->orWhereHas('equipo', fn ($e) => $e->where('serie', 'like', "%$q%"));
            });
        }
        if ($r->filled('estado')) {
            $query->where('estado_actual_id', $r->integer('estado'));
        }
        if ($r->filled('desde')) {
            $query->where('fecha_ingreso', '>=', CarbonImmutable::parse($r->input('desde'), 'America/Lima')->startOfDay()->utc());
        }
        if ($r->filled('hasta')) {
            $query->where('fecha_ingreso', '<=', CarbonImmutable::parse($r->input('hasta'), 'America/Lima')->endOfDay()->utc());
        }

        return view('pedidos.index', ['pedidos' => $query->latest('fecha_ingreso')->paginate(15)->withQueryString(), 'estados' => EstadoServicio::orderBy('orden')->get()]);
    }

    public function create()
    {
        return view('pedidos.create', ['clientes' => Cliente::orderBy('nombres')->get(), 'equipos' => Equipo::orderBy('id')->get()]);
    }

    public function store(Request $r, PedidoService $service)
    {
        $data = $r->validate(['cliente_id' => 'nullable|integer|exists:clientes,id', 'nombres' => 'required_without:cliente_id|nullable|string|max:150', 'documento' => 'nullable|string|max:20', 'telefono' => 'required_without:cliente_id|nullable|string|max:25', 'email' => 'nullable|email|max:190', 'equipo_id' => 'nullable|integer|exists:equipos,id', 'tipo' => 'required_without:equipo_id|nullable|string|max:60', 'marca' => 'nullable|string|max:80', 'modelo' => 'nullable|string|max:100', 'serie' => 'nullable|string|max:100', 'falla_reportada' => 'required|string|min:5|max:5000']);
        if (! empty($data['equipo_id']) && empty($data['cliente_id'])) {
            throw ValidationException::withMessages(['cliente_id' => 'Selecciona el cliente propietario del equipo existente.']);
        }
        $pedido = $service->registrar($data, $r->user());

        return redirect()->route('pedidos.show', $pedido)->with('ok', 'Pedido registrado. Entrega el código de seguimiento al cliente.');
    }

    public function show(PedidoServicio $pedido)
    {
        $pedido->load(['cliente', 'equipo', 'estado', 'registrador', 'historial.nuevo', 'historial.usuario', 'cotizaciones']);

        return view('pedidos.show', compact('pedido') + ['siguiente' => EstadoServicio::where('orden', $pedido->estado->orden + 1)->first()]);
    }

    public function estado(Request $r, PedidoServicio $pedido, PedidoService $service)
    {
        $data = $r->validate(['estado_actual_id' => 'required|integer|exists:estados_servicio,id', 'observacion' => 'required|string|min:3|max:1500']);
        $service->avanzar($pedido, $data['estado_actual_id'], $data['observacion'], $r->user());

        return back()->with('ok', 'Estado e historial actualizados.');
    }

    public function cobro(Request $r, PedidoServicio $pedido)
    {
        $d = $r->validate(['importe_cobrado' => 'required|numeric|decimal:0,2|min:0|max:999999999.99', 'fecha_cobro' => 'required|date_format:Y-m-d\TH:i']);
        $fecha = CarbonImmutable::createFromFormat('Y-m-d\TH:i', $d['fecha_cobro'], 'America/Lima')->utc();
        if ($fecha->lt($pedido->fecha_ingreso) || $fecha->gt(now())) {
            throw ValidationException::withMessages(['fecha_cobro' => 'La fecha debe estar entre el ingreso del pedido y el momento actual.']);
        }
        $pedido->update(['importe_cobrado' => $d['importe_cobrado'], 'fecha_cobro' => $fecha]);

        return back()->with('ok','Importe y fecha de cobro registrados para los reportes.');
    }
}
