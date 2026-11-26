<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\EstadoServicio;
use App\Models\HistorialEstado;
use App\Models\PedidoServicio;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PedidoService
{
    public function registrar(array $data, Usuario $actor): PedidoServicio
    {
        return DB::transaction(function () use ($data, $actor) {
            $cliente = ! empty($data['cliente_id']) ? Cliente::findOrFail($data['cliente_id']) : Cliente::create(collect($data)->only(['nombres', 'documento', 'telefono', 'email'])->all());
            if (! empty($data['equipo_id'])) {
                $equipo = Equipo::findOrFail($data['equipo_id']);
                if ($equipo->cliente_id !== $cliente->id) {
                    throw ValidationException::withMessages(['equipo_id' => 'El equipo no pertenece al cliente seleccionado.']);
                }
            } else {
                $equipo = $cliente->equipos()->create(collect($data)->only(['tipo', 'marca', 'modelo', 'serie'])->all());
            }
            $pedido = PedidoServicio::create(['codigo_seguimiento' => bin2hex(random_bytes(16)), 'cliente_id' => $cliente->id, 'equipo_id' => $equipo->id, 'registrado_por' => $actor->id, 'estado_actual_id' => 1, 'falla_reportada' => $data['falla_reportada'], 'fecha_ingreso' => now()]);
            HistorialEstado::create(['pedido_id' => $pedido->id, 'estado_anterior_id' => null, 'estado_nuevo_id' => 1, 'usuario_id' => $actor->id, 'fecha_cambio' => now(), 'observacion' => 'Equipo registrado en recepción.']);

            return $pedido;
        });
    }

    public function avanzar(PedidoServicio $pedido, int $nuevo, string $observacion, Usuario $actor): void
    {
        DB::transaction(function () use ($pedido, $nuevo, $observacion, $actor) {
            $locked = PedidoServicio::lockForUpdate()->findOrFail($pedido->id);
            $estado = EstadoServicio::findOrFail($nuevo);
            if ($estado->orden !== $locked->estado->orden + 1) {
                throw ValidationException::withMessages(['estado_actual_id' => 'Solo se permite avanzar al siguiente estado. Actualiza la página si otro usuario modificó el pedido.']);
            }
            HistorialEstado::create(['pedido_id' => $locked->id, 'estado_anterior_id' => $locked->estado_actual_id, 'estado_nuevo_id' => $nuevo, 'usuario_id' => $actor->id, 'fecha_cambio' => now(), 'observacion' => $observacion]);
            $locked->update(['estado_actual_id' => $nuevo, 'fecha_entrega' => $estado->codigo === 'ENTREGADO' ? now() : null]);
        });
    }
}
