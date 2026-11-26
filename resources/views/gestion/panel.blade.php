@extends('layouts.gestion')
@section('titulo','Panel de control')
@section('subtitulo','Resumen de los pedidos de servicio de AKS Soluciones.')
@section('acciones')@if(auth()->user()->es('ADMINISTRADOR','RECEPCIONISTA'))<a href="{{ route('pedidos.create') }}" class="btn btn-primary">Nuevo pedido</a>@endif @endsection
@section('contenido')
<div class="metric-grid">@foreach($estados as $e)<a href="{{ route('pedidos.index',['estado'=>$e->id]) }}" class="metric-card"><span>{{ $e->nombre }}</span><strong>{{ $conteos[$e->id]??0 }}</strong><small>Ver pedidos</small></a>@endforeach</div>
<div class="row g-4 mt-1"><div class="col-xl-8"><section class="surface"><div class="surface-heading"><h2>Pedidos recientes</h2><a href="{{ route('pedidos.index') }}">Ver todos</a></div>@include('pedidos.tabla',['pedidos'=>$recientes])</section></div><div class="col-xl-4"><section class="surface"><h2>Listos para entrega</h2>@forelse($listos as $p)<a class="ready-item" href="{{ route('pedidos.show',$p) }}"><strong>#{{ $p->id }} · {{ $p->cliente->nombres }}</strong><span>{{ $p->equipo->tipo }} · {{ $p->equipo->marca }}</span></a>@empty<p class="empty-copy">No hay equipos pendientes de recoger.</p>@endforelse</section><section class="surface mt-4"><h2>Tu perfil</h2><p class="mt-3">{{ auth()->user()->perfil->nombre }}</p><p class="small">Las acciones disponibles se muestran según tus permisos.</p></section></div></div>
@endsection
