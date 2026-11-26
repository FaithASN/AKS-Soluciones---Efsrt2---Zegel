<?php

namespace Database\Seeders;

use App\Models\EstadoServicio;
use App\Models\Perfil;
use App\Models\Servicio;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ([[1, 'ADMINISTRADOR', 'Administrador'], [2, 'TECNICO', 'Técnico'], [3, 'RECEPCIONISTA', 'Recepcionista']] as [$id,$codigo,$nombre]) {
            Perfil::firstOrCreate(['id' => $id], compact('codigo', 'nombre'));
        }
        foreach ([[1, 'RECIBIDO', 'Recibido'], [2, 'EN_DIAGNOSTICO', 'En diagnóstico'], [3, 'EN_REPARACION', 'En reparación'], [4, 'LISTO_PARA_ENTREGA', 'Listo para entrega'], [5, 'ENTREGADO', 'Entregado']] as [$id,$codigo,$nombre]) {
            EstadoServicio::firstOrCreate(['id' => $id], ['codigo' => $codigo, 'nombre' => $nombre, 'orden' => $id]);
        }
        foreach ([
            ['Mantenimiento de equipos', 'Limpieza y optimización para mejorar el funcionamiento del equipo.', 60],
            ['Reparación de hardware', 'Diagnóstico y reparación de componentes del equipo.', 80],
            ['Instalación de software', 'Instalación y configuración de sistemas y programas.', 40],
            ['Redes y conectividad', 'Configuración de redes, Wi-Fi y conectividad.', 90],
            ['Seguridad de datos', 'Respaldo y protección de la información del equipo.', 100],
            ['Soporte técnico', 'Orientación y atención técnica según la necesidad del equipo.', 50],
        ] as [$nombre,$descripcion,$precio]) {
            Servicio::firstOrCreate(['nombre' => $nombre], ['descripcion' => $descripcion, 'precio_vigente' => $precio, 'activo' => true]);
        }
    }
}
