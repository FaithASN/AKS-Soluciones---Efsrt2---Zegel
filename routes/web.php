<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PublicoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicoController::class, 'inicio'])->name('inicio');
Route::get('/servicios', [PublicoController::class, 'servicios'])->name('servicios');
Route::get('/seguimiento', [PublicoController::class, 'seguimiento'])->name('seguimiento');
Route::post('/seguimiento', [PublicoController::class, 'consultar'])->middleware('throttle:20,1')->name('seguimiento.consultar');
Route::middleware('guest')->group(function () {
    Route::get('/acceso', [AuthController::class, 'form'])->name('login');
    Route::post('/acceso', [AuthController::class, 'login'])->middleware('throttle:20,1')->name('login.submit');
});
Route::post('/salir', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::prefix('gestion')->middleware(['auth', 'perfil:ADMINISTRADOR,TECNICO,RECEPCIONISTA'])->group(function () {
    Route::get('/', PanelController::class)->name('panel');
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/nuevo', [PedidoController::class, 'create'])->middleware('perfil:ADMINISTRADOR,RECEPCIONISTA')->name('pedidos.create');
    Route::post('/pedidos', [PedidoController::class, 'store'])->middleware('perfil:ADMINISTRADOR,RECEPCIONISTA')->name('pedidos.store');
    Route::get('/pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show');
    Route::patch('/pedidos/{pedido}/estado', [PedidoController::class, 'estado'])->middleware('perfil:ADMINISTRADOR,TECNICO')->name('pedidos.estado');
    Route::patch('/pedidos/{pedido}/cobro', [PedidoController::class, 'cobro'])->middleware('perfil:ADMINISTRADOR')->name('pedidos.cobro');
    Route::middleware('perfil:ADMINISTRADOR,RECEPCIONISTA')->group(function () {
        Route::resource('clientes', ClienteController::class)->except(['show', 'destroy']);
    });
    Route::middleware('perfil:ADMINISTRADOR')->group(function () {
        Route::resource('catalogo', ServicioController::class)->parameters(['catalogo' => 'servicio'])->except(['show', 'destroy']);
        Route::resource('usuarios', UsuarioController::class)->except(['show', 'destroy']);
        Route::get('/cotizaciones/{cotizacion}/pdf', [CotizacionController::class, 'pdf'])->name('cotizaciones.pdf');
        Route::resource('cotizaciones', CotizacionController::class)->parameters(['cotizaciones' => 'cotizacion'])->only(['index', 'create', 'store', 'show']);
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/pdf', [ReporteController::class, 'pdf'])->name('reportes.pdf');
    });
});
