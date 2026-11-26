<?php

namespace App\Services;

use App\Models\EstadoServicio;
use App\Models\PedidoServicio;
use Carbon\CarbonImmutable;

class ReporteService
{
    public function generar(string $desde, string $hasta): array
    {
        $inicio = CarbonImmutable::parse($desde, 'America/Lima')->startOfDay()->utc();
        $fin = CarbonImmutable::parse($hasta, 'America/Lima')->endOfDay()->utc();
        $pedidos = PedidoServicio::with(['cliente', 'equipo', 'estado'])->whereBetween('fecha_ingreso', [$inicio, $fin])->orderByDesc('fecha_ingreso')->get();
        $cobros = PedidoServicio::with('cliente')->whereBetween('fecha_cobro', [$inicio, $fin])->orderBy('fecha_cobro')->get();
        $ingresos = $cobros->sum(fn ($p) => CotizacionService::centavos($p->importe_cobrado));
        $completados = $pedidos->filter(fn ($p) => $p->fecha_entrega !== null);
        $horas = $completados->isEmpty() ? null : $completados->avg(fn ($p) => $p->fecha_ingreso->diffInSeconds($p->fecha_entrega) / 3600);

        return compact('desde', 'hasta', 'pedidos', 'cobros', 'ingresos', 'horas') + ['estados' => EstadoServicio::orderBy('orden')->get(), 'total' => $pedidos->count(), 'entregados' => $completados->count()];
    }
}
