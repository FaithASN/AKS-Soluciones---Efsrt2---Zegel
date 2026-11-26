@extends('layouts.gestion')
@section('titulo','Cotizaciones')
@section('subtitulo','Documentos emitidos con descripción y precio conservados.')
@section('acciones')<a class="btn btn-primary" href="{{ route('cotizaciones.create') }}">Nueva cotización</a>@endsection
@section('contenido')<section class="surface"><div class="table-responsive"><table class="table"><thead><tr><th>Cotización</th><th>Pedido</th><th>Cliente</th><th>Emisión</th><th>Total</th><th>Acción</th></tr></thead><tbody>@forelse($cotizaciones as $q)<tr><td>#{{ $q->id }}</td><td>#{{ $q->pedido_id }}</td><td>{{ $q->pedido->cliente->nombres }}</td><td>{{ $q->fecha_emision->timezone('America/Lima')->format('d/m/Y') }}</td><td>S/ {{ number_format((float)$q->total,2) }}</td><td><a href="{{ route('cotizaciones.show',$q) }}">Ver cotización</a></td></tr>@empty<tr><td colspan="6" class="empty-copy">No se han emitido cotizaciones.</td></tr>@endforelse</tbody></table></div>{{ $cotizaciones->links() }}</section>@endsection
