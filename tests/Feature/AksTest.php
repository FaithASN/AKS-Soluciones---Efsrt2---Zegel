<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\PedidoServicio;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Services\CotizacionService;
use App\Services\PedidoService;
use App\Services\ReporteService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AksTest extends TestCase
{
    use RefreshDatabase;

    private int $mantenimiento;

    private int $instalacion;

    private int $hardware;

    private Usuario $admin;

    private Usuario $tecnico;

    private Usuario $recepcion;

    protected function beforeRefreshingDatabase()
    {
        if (! str_ends_with(config('database.connections.mysql.database'), '_test')) {
            throw new \RuntimeException('Las pruebas solo pueden ejecutarse sobre una base terminada en _test.');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->mantenimiento = Servicio::where('nombre', 'Mantenimiento de equipos')->value('id');
        $this->instalacion = Servicio::where('nombre', 'Instalación de software')->value('id');
        $this->hardware = Servicio::where('nombre', 'Reparación de hardware')->value('id');
        foreach (['admin' => 1, 'tecnico' => 2, 'recepcion' => 3] as $name => $role) {
            $this->$name = Usuario::create(['nombre' => $name, 'email' => $name.'@aks.test', 'perfil_id' => $role, 'password_hash' => Hash::make('PruebaSegura123!'), 'activo' => true]);
        }
    }

    private function datos(): array
    {
        return ['nombres' => 'Cliente ficticio', 'telefono' => '000000000', 'tipo' => 'Laptop', 'marca' => 'Prueba', 'modelo' => 'Modelo de prueba', 'falla_reportada' => 'No enciende al conectar el cargador.'];
    }

    private function pedido(): PedidoServicio
    {
        return app(PedidoService::class)->registrar($this->datos(), $this->recepcion);
    }

    public function test_las_paginas_publicas_y_acceso_se_renderizan(): void
    {
        foreach (['/', '/servicios', '/seguimiento', '/acceso'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_login_valida_cuenta_activa_y_cierra_sesion(): void
    {
        $this->post('/acceso', ['email' => $this->admin->email, 'password' => 'incorrecta'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/acceso', ['email' => $this->admin->email, 'password' => 'PruebaSegura123!'])->assertRedirect('/gestion');
        $this->assertAuthenticatedAs($this->admin);
        $this->post('/salir')->assertRedirect('/acceso');
        $this->assertGuest();
        $this->admin->update(['activo' => false]);
        $this->post('/acceso', ['email' => $this->admin->email, 'password' => 'PruebaSegura123!'])->assertSessionHasErrors('email');
    }

    public function test_todos_los_modulos_del_admin_renderizan(): void
    {
        $p = $this->pedido();
        $q = app(CotizacionService::class)->emitir(['pedido_id' => $p->id, 'items' => [['servicio_id' => $this->mantenimiento, 'cantidad' => 1]]], $this->admin);
        foreach (['/gestion', '/gestion/pedidos', '/gestion/pedidos/nuevo', '/gestion/pedidos/'.$p->id, '/gestion/clientes', '/gestion/clientes/create', '/gestion/clientes/'.$p->cliente_id.'/edit', '/gestion/catalogo', '/gestion/catalogo/create', '/gestion/catalogo/'.$this->mantenimiento.'/edit', '/gestion/usuarios', '/gestion/usuarios/create', '/gestion/usuarios/'.$this->admin->id.'/edit', '/gestion/cotizaciones', '/gestion/cotizaciones/create', '/gestion/cotizaciones/'.$q->id, '/gestion/reportes'] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    public function test_se_aplican_los_permisos_de_perfil(): void
    {
        $this->get('/gestion')->assertRedirect('/acceso');
        $this->actingAs($this->tecnico)->get('/gestion/pedidos/nuevo')->assertForbidden();
        foreach (['/gestion/usuarios', '/gestion/catalogo', '/gestion/cotizaciones', '/gestion/reportes', '/gestion/clientes'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $p = $this->pedido();
        $this->actingAs($this->recepcion)->patch('/gestion/pedidos/'.$p->id.'/estado', ['estado_actual_id' => 2, 'observacion' => 'Revisión iniciada'])->assertForbidden();
    }

    public function test_registro_persiste_cliente_equipo_pedido_e_historial(): void
    {
        $this->actingAs($this->recepcion)->post('/gestion/pedidos', $this->datos())->assertSessionHasNoErrors()->assertRedirect();
        $p = PedidoServicio::firstOrFail();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $p->codigo_seguimiento);
        $this->assertDatabaseCount('clientes', 1);
        $this->assertDatabaseCount('equipos', 1);
        $this->assertDatabaseHas('historial_estados', ['pedido_id' => $p->id, 'estado_anterior_id' => null, 'estado_nuevo_id' => 1]);
    }

    public function test_se_rechaza_equipo_de_otro_cliente_y_se_revierte_la_operacion(): void
    {
        $p = $this->pedido();
        $c = Cliente::create(['nombres' => 'Otro cliente', 'telefono' => '000']);
        $this->actingAs($this->recepcion)->post('/gestion/pedidos', ['cliente_id' => $c->id, 'equipo_id' => $p->equipo_id, 'falla_reportada' => 'No enciende el equipo.'])->assertSessionHasErrors('equipo_id');
        $this->assertDatabaseCount('pedidos_servicio', 1);
        $this->assertDatabaseCount('historial_estados', 1);
    }

    public function test_la_fk_compuesta_impide_propietarios_inconsistentes(): void
    {
        $p = $this->pedido();
        $c = Cliente::create(['nombres' => 'Otro cliente', 'telefono' => '000']);
        $this->expectException(QueryException::class);
        DB::table('pedidos_servicio')->where('id', $p->id)->update(['cliente_id' => $c->id]);
    }

    public function test_estados_consecutivos_historial_y_fecha_de_entrega(): void
    {
        $p = $this->pedido();
        $this->actingAs($this->tecnico)->patch('/gestion/pedidos/'.$p->id.'/estado', ['estado_actual_id' => 3, 'observacion' => 'Intento de salto'])->assertSessionHasErrors('estado_actual_id');
        $this->assertDatabaseCount('historial_estados', 1);
        foreach ([2, 3, 4, 5] as $estado) {
            $this->patch('/gestion/pedidos/'.$p->id.'/estado', ['estado_actual_id' => $estado, 'observacion' => 'Actualización técnica'])->assertSessionHasNoErrors()->assertRedirect();
        }
        $this->assertDatabaseCount('historial_estados', 5);
        $this->assertNotNull($p->fresh()->fecha_entrega);
        $this->assertSame(5, $p->fresh()->estado_actual_id);
        $this->patch('/gestion/pedidos/'.$p->id.'/estado', ['estado_actual_id' => 5, 'observacion' => 'Doble envío'])->assertSessionHasErrors('estado_actual_id');
        $this->assertDatabaseCount('historial_estados', 5);
    }

    public function test_cotizacion_recalcula_total_y_preserva_precios_historicos(): void
    {
        $p = $this->pedido();
        $this->actingAs($this->admin)->post('/gestion/cotizaciones', ['pedido_id' => $p->id, 'total' => '0.01', 'items' => [['servicio_id' => $this->mantenimiento, 'cantidad' => 2, 'precio_unitario' => '0.01'], ['servicio_id' => $this->instalacion, 'cantidad' => 1]]])->assertSessionHasNoErrors()->assertRedirect();
        $q = Cotizacion::firstOrFail();
        $this->assertSame('160.00', $q->total);
        $this->assertSame('120.00', $q->detalles->first()->subtotal);
        Servicio::find($this->mantenimiento)->update(['precio_vigente' => '500.00', 'nombre' => 'Nuevo nombre']);
        $this->assertSame('60.00', $q->detalles()->first()->precio_unitario);
        $this->assertSame('Mantenimiento de equipos', $q->detalles()->first()->descripcion_servicio);
    }

    public function test_cotizacion_rechaza_servicios_inactivos_y_cantidades_invalidas(): void
    {
        $p = $this->pedido();
        Servicio::find($this->mantenimiento)->update(['activo' => false]);
        $this->actingAs($this->admin)->post('/gestion/cotizaciones', ['pedido_id' => $p->id, 'items' => [['servicio_id' => $this->mantenimiento, 'cantidad' => 1]]])->assertSessionHasErrors('items.0.servicio_id');
        $this->assertDatabaseCount('cotizaciones', 0);
        $this->post('/gestion/cotizaciones', ['pedido_id' => $p->id, 'items' => [['servicio_id' => $this->hardware, 'cantidad' => 0]]])->assertSessionHasErrors('items.0.cantidad');
    }

    public function test_consulta_publica_no_expone_datos_privados(): void
    {
        $p = $this->pedido();
        $this->post('/seguimiento', ['codigo' => $p->codigo_seguimiento])->assertOk()->assertSee('Recibido')->assertDontSee('Cliente ficticio')->assertDontSee('000000000')->assertDontSee('No enciende al conectar el cargador.');
        $this->post('/seguimiento', ['codigo' => str_repeat('f', 32)])->assertOk()->assertSee('No encontramos ese código');
        $this->post('/seguimiento', ['codigo' => 'incorrecto'])->assertSessionHasErrors('codigo');
    }

    public function test_usuario_desactivado_pierde_el_acceso_y_admin_no_puede_desactivarse(): void
    {
        $this->actingAs($this->tecnico);
        $this->tecnico->update(['activo' => false]);
        $this->get('/gestion')->assertRedirect('/acceso');
        $this->actingAs($this->admin)->put('/gestion/usuarios/'.$this->admin->id, ['nombre' => 'Admin', 'email' => $this->admin->email, 'perfil_id' => 1, 'activo' => 0])->assertSessionHasErrors('activo');
        $this->assertTrue($this->admin->fresh()->activo);
    }

    public function test_gestion_de_clientes_servicios_y_usuarios(): void
    {
        $this->actingAs($this->admin)->post('/gestion/clientes', ['nombres' => 'Cliente nuevo', 'telefono' => '000'])->assertSessionHasNoErrors();
        $this->post('/gestion/catalogo', ['nombre' => 'Servicio nuevo', 'precio_vigente' => '25.50', 'activo' => 1])->assertSessionHasNoErrors();
        $this->post('/gestion/usuarios', ['nombre' => 'Nuevo técnico', 'email' => 'nuevo@aks.test', 'perfil_id' => 2, 'activo' => 1, 'password' => 'PruebaSegura123!', 'password_confirmation' => 'PruebaSegura123!'])->assertSessionHasNoErrors();
        $u = Usuario::where('email', 'nuevo@aks.test')->firstOrFail();
        $this->assertTrue(Hash::check('PruebaSegura123!', $u->password_hash));
        $this->put('/gestion/catalogo/'.$this->mantenimiento, ['nombre' => 'Mantenimiento', 'precio_vigente' => '75.00', 'activo' => 0])->assertSessionHasNoErrors();
        $this->get('/servicios')->assertDontSee('Mantenimiento</h3>', false);
    }

    public function test_cobros_reportes_y_fechas_de_lima(): void
    {
        $p = $this->pedido();
        $p->update(['fecha_ingreso' => now()->subDays(3)]);
        $fecha = now('America/Lima')->subMinute()->format('Y-m-d\TH:i');
        $this->actingAs($this->admin)->patch('/gestion/pedidos/'.$p->id.'/cobro', ['importe_cobrado' => '125.50', 'fecha_cobro' => $fecha])->assertSessionHasNoErrors();
        $report = app(ReporteService::class)->generar(now('America/Lima')->subDay()->toDateString(), now('America/Lima')->toDateString());
        $this->assertSame(12550, $report['ingresos']);
        $this->assertSame(0, $report['total']);
        $this->assertNull($report['horas']);
        $this->patch('/gestion/pedidos/'.$p->id.'/cobro', ['importe_cobrado' => '5', 'fecha_cobro' => now()->addDays(5)->format('Y-m-d\TH:i')])->assertSessionHasErrors('fecha_cobro');
    }

    public function test_pdf_de_cotizacion_y_reporte_son_documentos_pdf(): void
    {
        $p = $this->pedido();
        $q = app(CotizacionService::class)->emitir(['pedido_id' => $p->id, 'items' => [['servicio_id' => $this->mantenimiento, 'cantidad' => 1]]], $this->admin);
        foreach (['/gestion/cotizaciones/'.$q->id.'/pdf', '/gestion/reportes/pdf'] as $url) {
            $response = $this->actingAs($this->admin)->get($url)->assertOk()->assertHeader('Content-Type','application/pdf');
            $this->assertStringStartsWith('%PDF-',$response->getContent());
        }
    }
}
