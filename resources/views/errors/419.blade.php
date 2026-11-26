@extends('layouts.publico')
@section('contenido')<section class="section"><div class="container"><span class="eyebrow">SESIÓN CADUCADA</span><h1>Actualiza la página e inténtalo otra vez</h1><p class="mt-3">Por seguridad, los formularios caducan al finalizar la sesión.</p><a class="button button-primary mt-4" href="{{ route('login') }}">Iniciar sesión</a></div></section>@endsection
