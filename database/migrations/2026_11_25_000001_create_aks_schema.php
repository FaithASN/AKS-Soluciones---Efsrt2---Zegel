<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfiles', function (Blueprint $t) {
            $t->unsignedTinyInteger('id')->primary();
            $t->string('codigo', 20)->unique();
            $t->string('nombre', 50);
        });
        Schema::create('usuarios', function (Blueprint $t) {
            $t->id();
            $t->unsignedTinyInteger('perfil_id');
            $t->string('nombre', 120);
            $t->string('email', 190)->unique();
            $t->string('password_hash');
            $t->boolean('activo')->default(true);
            $t->dateTime('created_at')->useCurrent();
            $t->dateTime('updated_at')->nullable();
            $t->foreign('perfil_id')->references('id')->on('perfiles')->restrictOnDelete()->restrictOnUpdate();
        });
        Schema::create('clientes', function (Blueprint $t) {
            $t->id();
            $t->string('nombres', 150);
            $t->string('documento', 20)->nullable()->index();
            $t->string('telefono', 25)->index();
            $t->string('email', 190)->nullable();
            $t->dateTime('created_at')->useCurrent();
            $t->dateTime('updated_at')->nullable();
        });
        Schema::create('estados_servicio', function (Blueprint $t) {
            $t->unsignedTinyInteger('id')->primary();
            $t->string('codigo', 32)->unique();
            $t->string('nombre', 60);
            $t->unsignedTinyInteger('orden')->unique();
        });
        Schema::create('equipos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete()->restrictOnUpdate();
            $t->string('tipo', 60);
            $t->string('marca', 80)->nullable();
            $t->string('modelo', 100)->nullable();
            $t->string('serie', 100)->nullable()->index();
            $t->dateTime('created_at')->useCurrent();
            $t->dateTime('updated_at')->nullable();
            $t->unique(['id', 'cliente_id']);
        });
        Schema::create('pedidos_servicio', function (Blueprint $t) {
            $t->id();
            $t->char('codigo_seguimiento', 32)->unique();
            $t->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete()->restrictOnUpdate();
            $t->unsignedBigInteger('equipo_id');
            $t->foreignId('registrado_por')->constrained('usuarios')->restrictOnDelete()->restrictOnUpdate();
            $t->unsignedTinyInteger('estado_actual_id');
            $t->text('falla_reportada');
            $t->dateTime('fecha_ingreso')->useCurrent();
            $t->dateTime('fecha_entrega')->nullable();
            $t->decimal('importe_cobrado', 12, 2)->nullable();
            $t->dateTime('fecha_cobro')->nullable()->index();
            $t->dateTime('updated_at')->nullable();
            $t->foreign('estado_actual_id')->references('id')->on('estados_servicio')->restrictOnDelete()->restrictOnUpdate();
            $t->foreign(['equipo_id', 'cliente_id'])->references(['id', 'cliente_id'])->on('equipos')->restrictOnDelete()->restrictOnUpdate();
            $t->index(['estado_actual_id', 'fecha_ingreso']);
        });
        Schema::create('historial_estados', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pedido_id')->constrained('pedidos_servicio')->restrictOnDelete()->restrictOnUpdate();
            $t->unsignedTinyInteger('estado_anterior_id')->nullable();
            $t->unsignedTinyInteger('estado_nuevo_id');
            $t->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete()->restrictOnUpdate();
            $t->dateTime('fecha_cambio')->useCurrent();
            $t->text('observacion')->nullable();
            $t->foreign('estado_anterior_id')->references('id')->on('estados_servicio')->restrictOnDelete()->restrictOnUpdate();
            $t->foreign('estado_nuevo_id')->references('id')->on('estados_servicio')->restrictOnDelete()->restrictOnUpdate();
            $t->index(['pedido_id', 'fecha_cambio', 'id']);
        });
        Schema::create('servicios', function (Blueprint $t) {
            $t->id();
            $t->string('nombre', 120);
            $t->text('descripcion')->nullable();
            $t->decimal('precio_vigente', 12, 2);
            $t->boolean('activo')->default(true);
            $t->dateTime('created_at')->useCurrent();
            $t->dateTime('updated_at')->nullable();
            $t->index(['activo', 'nombre']);
        });
        Schema::create('cotizaciones', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pedido_id')->constrained('pedidos_servicio')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('emitida_por')->constrained('usuarios')->restrictOnDelete()->restrictOnUpdate();
            $t->dateTime('fecha_emision')->useCurrent()->index();
            $t->decimal('total', 12, 2);
            $t->text('observaciones')->nullable();
            $t->dateTime('updated_at')->nullable();
        });
        Schema::create('detalles_cotizacion', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cotizacion_id')->constrained('cotizaciones')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('servicio_id')->constrained('servicios')->restrictOnDelete()->restrictOnUpdate();
            $t->string('descripcion_servicio', 255);
            $t->unsignedSmallInteger('cantidad');
            $t->decimal('precio_unitario', 12, 2);
            $t->decimal('subtotal', 12, 2)->storedAs('round(cantidad * precio_unitario, 2)');
        });
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE servicios ADD CONSTRAINT servicios_precio_positivo CHECK (precio_vigente >= 0)');
            DB::statement('ALTER TABLE detalles_cotizacion ADD CONSTRAINT detalle_valido CHECK (cantidad > 0 AND precio_unitario >= 0)');
            DB::statement('ALTER TABLE cotizaciones ADD CONSTRAINT cotizacion_total_positivo CHECK (total >= 0)');
            DB::statement('ALTER TABLE pedidos_servicio ADD CONSTRAINT entrega_valida CHECK (fecha_entrega IS NULL OR (fecha_entrega >= fecha_ingreso AND estado_actual_id = 5))');
            DB::statement('ALTER TABLE pedidos_servicio ADD CONSTRAINT cobro_valido CHECK ((importe_cobrado IS NULL AND fecha_cobro IS NULL) OR (importe_cobrado IS NOT NULL AND importe_cobrado >= 0 AND fecha_cobro IS NOT NULL AND fecha_cobro >= fecha_ingreso))');
        }
    }

    public function down(): void
    {
        foreach (['detalles_cotizacion', 'cotizaciones', 'servicios', 'historial_estados', 'pedidos_servicio', 'equipos', 'estados_servicio', 'clientes', 'usuarios', 'perfiles'] as $name) {
            Schema::dropIfExists($name);
        }
    }
};
