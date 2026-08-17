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

/*
|--------------------------------------------------------------------------
| Ruta Raíz (Redirección automática al Login)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Autenticación (Protección contra Fuerza Bruta con throttle:5,1)
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'login'])->name('login');

// Máximo 5 intentos de login por minuto por dirección IP
Route::post('/login', [AuthController::class, 'autenticar'])
    ->middleware('throttle:5,1')
    ->name('login.autenticar');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (Solo entran usuarios con Sesión Iniciada)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Rutas compartidas (Tanto Administrador como Trabajador pueden entrar)
    |--------------------------------------------------------------------------
    */
    Route::get('/api/dashboard', [App\Http\Controllers\Api\Esp32Controller::class, 'dashboard']);
    Route::get('/api/temperaturas', [App\Http\Controllers\Api\Esp32Controller::class, 'temperaturas']);
    Route::patch('/alertas/{id}/atender', [AlertaController::class, 'atender'])->name('alertas.atender');
    Route::get('/obtenerDatosGrafico', [App\Http\Controllers\Api\Esp32Controller::class, 'obtenerDatosGrafico']);

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Producción
    Route::get('/produccion', [ProduccionController::class, 'index']);
    Route::post('/produccion/iniciar', [ProduccionController::class, 'iniciar']);
    Route::post('/produccion/finalizar', [ProduccionController::class, 'finalizar']);

    // Control del Sistema
    Route::get('/control', [ControlController::class, 'index']);
    Route::post('/control/motor/{estado}', [ControlController::class, 'motor']);
    Route::post('/control/ventilador/{estado}', [ControlController::class, 'ventilador']);
    Route::post('/control/sensor/{estado}', [ControlController::class, 'sensor']);
    Route::post('/control/{tipo}/modo', [App\Http\Controllers\ControlController::class, 'cambiarModo']);

    // Alertas y Eventos
    Route::get('/alertas', [AlertaController::class, 'index']);
    Route::get('/eventos', [EventoController::class, 'index']);

    // Reportes
    Route::get('/reportes', [ReporteController::class, 'index']);
    Route::get('/reportes/excel', [ReporteController::class, 'excel'])->name('reportes.excel');
    Route::get('/reportes/{id}', [ReporteController::class, 'show']);
    Route::get('/reportes/{id}/pdf', [ReporteController::class, 'pdf']);

    /*
    |--------------------------------------------------------------------------
    | Rutas Exclusivas de Administrador (Bloqueo por Middleware)
    |--------------------------------------------------------------------------
    */
    Route::middleware('rol:Administrador')->group(function () {

        // Productos
        Route::get('/productos', [ProductoController::class, 'index']);
        Route::get('/productos/create', [ProductoController::class, 'create']);
        Route::post('/productos', [ProductoController::class, 'store']);
        Route::get('/productos/{producto}/edit', [ProductoController::class, 'edit']);
        Route::put('/productos/{producto}', [ProductoController::class, 'update']);
        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy']);

        // Usuarios (CRUD Completo)
        Route::resource('usuarios', UserController::class);

    });

});