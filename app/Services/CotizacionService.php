<?php

namespace App\Services;

use App\Models\Cotizacion;
use App\Models\PedidoServicio;
use App\Models\Servicio;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CotizacionService
{
    public static function centavos(string $value): int
    {
        [$whole,$fraction] = array_pad(explode('.', $value, 2), 2, '');

        return (int) $whole * 100 + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    public static function decimal(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public function emitir(array $data, Usuario $actor): Cotizacion
    {
        return DB::transaction(function () use ($data, $actor) {
            PedidoServicio::lockForUpdate()->findOrFail($data['pedido_id']);
            $servicios = Servicio::whereIn('id', array_column($data['items'], 'servicio_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $items = [];
            $total = 0;
            foreach ($data['items'] as $i => $item) {
                $s = $servicios->get($item['servicio_id']);
                if (! $s || ! $s->activo) {
                    throw ValidationException::withMessages(["items.$i.servicio_id" => 'El servicio ya no está disponible.']);
                }
                $cents = self::centavos($s->precio_vigente) * (int) $item['cantidad'];
                $total += $cents;
                $items[] = ['servicio_id' => $s->id, 'descripcion_servicio' => $s->nombre, 'cantidad' => (int) $item['cantidad'], 'precio_unitario' => $s->precio_vigente];
            }
            if ($total > 999999999999) {
                throw ValidationException::withMessages(['items' => 'El total supera el límite permitido.']);
            }
            $quote = Cotizacion::create(['pedido_id' => $data['pedido_id'], 'emitida_por' => $actor->id, 'fecha_emision' => now(), 'total' => self::decimal($total), 'observaciones' => $data['observaciones'] ?? null]);
            foreach ($items as $item) {
                $quote->detalles()->create($item);
            }

            return $quote;
        });
    }
}
