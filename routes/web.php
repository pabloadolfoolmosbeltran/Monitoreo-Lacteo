<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProduccionController;
use App\Http\Controllers\ControlController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\PresentacionController;

Route::get('/', function () {
    return redirect()->route('catalogo.index');
});

Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
Route::get('/catalogo/nosotros', [CatalogoController::class, 'nosotros'])->name('catalogo.nosotros');
Route::get('/catalogo/contacto', [CatalogoController::class, 'contacto'])->name('catalogo.contacto');
Route::get('/catalogo/{presentacion}', [CatalogoController::class, 'detalle'])->name('catalogo.detalle');

Route::get('/login', [AuthController::class, 'login'])->name('login');

Route::post('/login', [AuthController::class, 'autenticar'])
    ->middleware('throttle:5,1')
    ->name('login.autenticar');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/api/dashboard', [App\Http\Controllers\Api\Esp32Controller::class, 'dashboard']);
    Route::get('/api/temperaturas', [App\Http\Controllers\Api\Esp32Controller::class, 'temperaturas']);
    Route::patch('/alertas/{id}/atender', [AlertaController::class, 'atender'])->name('alertas.atender');
    Route::get('/obtenerDatosGrafico', [App\Http\Controllers\Api\Esp32Controller::class, 'obtenerDatosGrafico']);

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/produccion', [ProduccionController::class, 'index']);
    Route::post('/produccion/iniciar', [ProduccionController::class, 'iniciar']);
    Route::post('/produccion/finalizar', [ProduccionController::class, 'finalizar']);

    Route::get('/control', [ControlController::class, 'index']);
    Route::post('/control/motor/{estado}', [ControlController::class, 'motor']);
    Route::post('/control/ventilador/{estado}', [ControlController::class, 'ventilador']);
    Route::post('/control/sensor/{estado}', [ControlController::class, 'sensor']);
    Route::post('/control/{tipo}/modo', [App\Http\Controllers\ControlController::class, 'cambiarModo']);

    Route::get('/alertas', [AlertaController::class, 'index']);
    Route::get('/eventos', [EventoController::class, 'index']);

    Route::get('/reportes', [ReporteController::class, 'index']);
    Route::get('/reportes/excel', [ReporteController::class, 'excel'])->name('reportes.excel');
    Route::get('/reportes/{id}', [ReporteController::class, 'show']);
    Route::get('/reportes/{id}/pdf', [ReporteController::class, 'pdf']);

    // Acceso para Administrador y Trabajador: ver, crear y editar (no eliminar)
    Route::middleware('rol:Administrador,Trabajador')->group(function () {

        Route::get('/productos', [ProductoController::class, 'index']);
        Route::get('/productos/create', [ProductoController::class, 'create']);
        Route::post('/productos', [ProductoController::class, 'store']);
        Route::get('/productos/{producto}/edit', [ProductoController::class, 'edit']);
        Route::put('/productos/{producto}', [ProductoController::class, 'update']);

        Route::get('/presentaciones', [PresentacionController::class, 'index'])->name('presentaciones.index');
        Route::get('/presentaciones/create', [PresentacionController::class, 'create'])->name('presentaciones.create');
        Route::post('/presentaciones', [PresentacionController::class, 'store'])->name('presentaciones.store');
        Route::get('/presentaciones/{presentacion}/edit', [PresentacionController::class, 'edit'])->name('presentaciones.edit');
        Route::put('/presentaciones/{presentacion}', [PresentacionController::class, 'update'])->name('presentaciones.update');

    });

    // Solo Administrador puede eliminar
    Route::middleware('rol:Administrador')->group(function () {

        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])
            ->name('productos.destroy');
        Route::delete('/presentaciones/{presentacion}', [PresentacionController::class, 'destroy'])
            ->name('presentaciones.destroy');

        Route::resource('usuarios', UserController::class);

    });

});
