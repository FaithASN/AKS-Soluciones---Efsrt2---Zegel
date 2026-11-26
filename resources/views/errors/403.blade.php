@extends('layouts.publico')
@section('contenido')<section class="section"><div class="container"><span class="eyebrow">ACCESO RESTRINGIDO</span><h1>Tu perfil no tiene permiso para esta acción</h1><p class="mt-3">Vuelve a tu panel para continuar con las funciones autorizadas.</p><a class="button button-primary mt-4" href="{{ route('panel') }}">Volver al panel</a></div></section>@endsection
