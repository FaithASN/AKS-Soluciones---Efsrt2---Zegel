<?php

namespace Database\Seeders;

use App\Models\PedidoServicio;
use App\Models\Usuario;
use App\Services\CotizacionService;
use App\Services\PedidoService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Los datos ficticios solo se cargan en local o testing.');
        }
        $password = env('DEMO_PASSWORD');
        if (! is_string($password) || strlen($password) < 10) {
            throw new \RuntimeException('Define DEMO_PASSWORD con al menos 10 caracteres en tu .env local.');
        }
        $this->call(DatabaseSeeder::class);
        $actors = [];
        foreach ([[1, 'Administrador de demostración', 'admin@aks.test'], [2, 'Técnico de demostración', 'tecnico@aks.test'], [3, 'Recepcionista de demostración', 'recepcion@aks.test']] as [$perfil_id,$nombre,$email]) {
            $actors[$perfil_id] = Usuario::firstOrCreate(['email' => $email], ['nombre' => $nombre, 'perfil_id' => $perfil_id, 'password_hash' => Hash::make($password), 'activo' => true]);
        }
        if (PedidoServicio::exists()) {
            return;
        }
        $nombres = ['Cliente de prueba A', 'Cliente de prueba B', 'Cliente de prueba C', 'Cliente de prueba D', 'Cliente de prueba E', 'Cliente de prueba F', 'Cliente de prueba G', 'Cliente de prueba H'];
        foreach ([1, 1, 2, 2, 3, 3, 4, 5] as $index => $state) {
            $p = app(PedidoService::class)->registrar(['nombres' => $nombres[$index], 'telefono' => '000000000', 'tipo' => $index % 2 ? 'PC de escritorio' : 'Laptop', 'marca' => $index % 2 ? 'Genérica' : 'Lenovo', 'modelo' => 'Equipo de demostración', 'serie' => 'DEMO-'.($index + 1), 'falla_reportada' => 'Datos ficticios: el equipo requiere revisión técnica.'], $actors[3]);
            $fecha = now()->subDays(9 - $index);
            $p->update(['fecha_ingreso' => $fecha]);
            $p->historial()->update(['fecha_cambio' => $fecha]);
            for ($s = 2; $s <= $state; $s++) {
                app(PedidoService::class)->avanzar($p, $s, 'Actualización de demostración.', $actors[2]);
            }
            if ($state >= 3) {
                app(CotizacionService::class)->emitir(['pedido_id' => $p->id, 'items' => [['servicio_id' => 1, 'cantidad' => 1], ['servicio_id' => 3, 'cantidad' => 1]], 'observaciones' => 'Cotización de demostración.'], $actors[1]);
            }
            if ($state === 5) {
                $p->update(['importe_cobrado' => '100.00', 'fecha_cobro' => now()]);
            }
        }
    }
}
