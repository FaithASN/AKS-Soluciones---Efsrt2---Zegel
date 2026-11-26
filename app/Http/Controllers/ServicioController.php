<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    public function index()
    {
        return view('servicios.index', ['servicios' => Servicio::orderBy('id')->paginate(15)]);
    }

    public function create()
    {
        return view('servicios.form', ['servicio' => new Servicio(['activo' => true])]);
    }

    public function edit(Servicio $servicio)
    {
        return view('servicios.form', compact('servicio'));
    }

    public function store(Request $r)
    {
        Servicio::create($this->datos($r));

        return redirect()->route('catalogo.index')->with('ok', 'Servicio registrado.');
    }

    public function update(Request $r, Servicio $servicio)
    {
        $servicio->update($this->datos($r));

        return redirect()->route('catalogo.index')->with('ok', 'Servicio actualizado. Las cotizaciones anteriores conservan sus precios.');
    }

    private function datos(Request $r)
    {
        $d = $r->validate(['nombre' => 'required|string|max:120', 'descripcion' => 'nullable|string|max:2000', 'precio_vigente' => 'required|numeric|decimal:0,2|min:0|max:999999.99', 'activo' => 'required|boolean']);

        return $d;
    }
}
