<?php

namespace App\Http\Controllers;

use App\Models\Perfil;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UsuarioController extends Controller
{
    public function index()
    {
        return view('usuarios.index', ['usuarios' => Usuario::with('perfil')->orderBy('id')->paginate(15)]);
    }

    public function create()
    {
        return view('usuarios.form', ['usuario' => new Usuario(['activo' => true]), 'perfiles' => Perfil::all()]);
    }

    public function edit(Usuario $usuario)
    {
        return view('usuarios.form', compact('usuario') + ['perfiles' => Perfil::all()]);
    }

    public function store(Request $r)
    {
        $d = $this->datos($r);
        $d['password_hash'] = Hash::make($d['password']);
        unset($d['password']);
        Usuario::create($d);

        return redirect()->route('usuarios.index')->with('ok', 'Usuario creado.');
    }

    public function update(Request $r, Usuario $usuario)
    {
        $d = $this->datos($r, $usuario);
        DB::transaction(function () use ($r, $usuario, $d) {
            $admins = Usuario::where('perfil_id', 1)->where('activo', true)->orderBy('id')->lockForUpdate()->get();
            $locked = Usuario::lockForUpdate()->findOrFail($usuario->id);
            if ($locked->id === $r->user()->id && (! $d['activo'] || (int) $d['perfil_id'] !== 1)) {
                throw ValidationException::withMessages(['activo' => 'No puedes desactivar tu cuenta ni cambiar tu propio perfil de administrador.']);
            }
            if ($locked->perfil_id === 1 && $locked->activo && (! $d['activo'] || (int) $d['perfil_id'] !== 1) && $admins->count() <= 1) {
                throw ValidationException::withMessages(['activo' => 'Debe conservarse al menos un administrador activo.']);
            }
            if (! empty($d['password'])) {
                $d['password_hash'] = Hash::make($d['password']);
            }unset($d['password']);
            $locked->update($d);
        });

        return redirect()->route('usuarios.index')->with('ok', 'Usuario actualizado.');
    }

    private function datos(Request $r, ?Usuario $u = null)
    {
        return $r->validate(['nombre' => 'required|string|max:120', 'email' => ['required', 'email', 'max:190', Rule::unique('usuarios')->ignore($u?->id)], 'perfil_id' => 'required|integer|exists:perfiles,id', 'activo' => 'required|boolean', 'password' => [$u ? 'nullable' : 'required', 'string', 'min:10', 'max:72', 'confirmed']]);
    }
}
