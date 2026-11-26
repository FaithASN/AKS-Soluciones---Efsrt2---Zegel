<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $r)
    {
        $r->validate(['q' => 'nullable|string|max:100']);
        $q = $r->string('q')->trim()->value();

        return view('clientes.index', ['clientes' => Cliente::withCount('equipos')->when($q, fn ($b) => $b->where(fn ($x) => $x->where('nombres', 'like', "%$q%")->orWhere('documento', 'like', "%$q%")))->orderBy('nombres')->paginate(15)->withQueryString()]);
    }

    public function create()
    {
        return view('clientes.form', ['cliente' => new Cliente]);
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.form', compact('cliente'));
    }

    public function store(Request $r)
    {
        Cliente::create($this->datos($r));

        return redirect()->route('clientes.index')->with('ok', 'Cliente registrado.');
    }

    public function update(Request $r, Cliente $cliente)
    {
        $cliente->update($this->datos($r));

        return redirect()->route('clientes.index')->with('ok', 'Datos del cliente actualizados.');
    }

    private function datos(Request $r)
    {
        return $r->validate(['nombres' => 'required|string|max:150', 'documento' => 'nullable|string|max:20', 'telefono' => 'required|string|max:25', 'email' => 'nullable|email|max:190']);
    }
}
