@extends('layouts.gestion')
@section('titulo','Usuarios y perfiles')
@section('subtitulo','Personal autorizado para acceder al sistema.')
@section('acciones')<a class="btn btn-primary" href="{{ route('usuarios.create') }}">Nuevo usuario</a>@endsection
@section('contenido')<section class="surface"><div class="table-responsive"><table class="table"><thead><tr><th>Nombre</th><th>Correo</th><th>Perfil</th><th>Estado</th><th>Acción</th></tr></thead><tbody>@foreach($usuarios as $u)<tr><td>{{ $u->nombre }}</td><td>{{ $u->email }}</td><td>{{ $u->perfil->nombre }}</td><td><span class="state {{ $u->activo?'state-4':'state-1' }}">{{ $u->activo?'Activo':'Inactivo' }}</span></td><td><a href="{{ route('usuarios.edit',$u) }}">Editar</a></td></tr>@endforeach</tbody></table></div>{{ $usuarios->links() }}</section>@endsection
