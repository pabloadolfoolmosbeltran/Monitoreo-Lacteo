<?php

use App\Http\Controllers\AjusteInventarioController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\Api\Esp32Controller;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ComercialController;
use App\Http\Controllers\ControlController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DescarteProductoController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\PresentacionController;
use App\Http\Controllers\ProduccionController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProductorController;
use App\Http\Controllers\ReporteComercialController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\OperadorComercial;
use Illuminate\Support\Facades\Route;

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

    Route::get('/dashboard/datos', [Esp32Controller::class, 'dashboard'])->name('dashboard.datos');
    Route::get('/dashboard/temperaturas', [Esp32Controller::class, 'temperaturas'])->name('dashboard.temperaturas');
    Route::patch('/alertas/{id}/atender', [AlertaController::class, 'atender'])->name('alertas.atender');
    Route::get('/obtenerDatosGrafico', [Esp32Controller::class, 'obtenerDatosGrafico']);

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/produccion', [ProduccionController::class, 'index']);
    Route::post('/produccion/iniciar', [ProduccionController::class, 'iniciar']);
    Route::post('/produccion/finalizar', [ProduccionController::class, 'finalizar']);

    Route::get('/control', [ControlController::class, 'index']);
    Route::post('/control/motor/{estado}', [ControlController::class, 'motor']);
    Route::post('/control/ventilador/{estado}', [ControlController::class, 'ventilador']);
    Route::post('/control/sensor/{estado}', [ControlController::class, 'sensor']);
    Route::post('/control/{tipo}/modo', [ControlController::class, 'cambiarModo']);

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

        Route::patch('/productos/{producto}/restablecer', [ProductoController::class, 'restore'])
            ->name('productos.restore');
        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])
            ->name('productos.destroy');
        Route::patch('/presentaciones/{presentacion}/restablecer', [PresentacionController::class, 'restore'])
            ->name('presentaciones.restore');
        Route::delete('/presentaciones/{presentacion}', [PresentacionController::class, 'destroy'])
            ->name('presentaciones.destroy');
        Route::patch('/usuarios/{usuario}/restablecer', [UserController::class, 'restore'])
            ->name('usuarios.restore');
        Route::patch('/productores/{productor}/restablecer', [ProductorController::class, 'restore'])
            ->whereNumber('productor')
            ->name('productores.restore');

        // ¿Qué hace? Expone la gestión de usuarios sin una pantalla de detalle inexistente.
        Route::resource('usuarios', UserController::class)->except(['show']);

    });

    Route::middleware(OperadorComercial::class)->group(function () {
        Route::get('/pos', [ComercialController::class, 'pos'])->name('pos.index');
        Route::get('/comercial/catalogo', [ComercialController::class, 'catalogo'])->name('ventas.catalogo');
        Route::post('/comercial/ventas', [ComercialController::class, 'vender'])->name('ventas.store');

        Route::resource('productores', ProductorController::class)
            ->parameters(['productores' => 'productor'])
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

        Route::get('/ingresos-productores', [ComercialController::class, 'ingresos'])->name('ingresos-productores.index');
        Route::get('/ingresos-productores/create', [ComercialController::class, 'crearIngreso'])->name('ingresos-productores.create');
        Route::post('/ingresos-productores', [ComercialController::class, 'guardarIngreso'])->name('ingresos-productores.store');
        Route::get('/ingresos-productores/{ingreso}', [ComercialController::class, 'detalle'])->name('ingresos-productores.show');
        Route::get('/ingresos-productores/{ingreso}/detalle-json', [ComercialController::class, 'detalleJson'])->name('ingresos-productores.detalle-json');
        Route::get('/ingresos-productores/{ingreso}/pdf', [ReporteComercialController::class, 'pdfIngreso'])->name('ingresos-productores.pdf');
        Route::get('/ingresos-productores/{ingreso}/excel', [ReporteComercialController::class, 'excelIngreso'])->name('ingresos-productores.excel');
        Route::get('/inventario', [ComercialController::class, 'inventario'])->name('inventario.index');
        Route::get('/inventario-general', [ComercialController::class, 'inventarioGeneral'])->name('inventario-general.index');
        Route::post('/lotes/{item}/movimientos', [ComercialController::class, 'movimiento'])->name('lotes.movimientos.store');
        Route::patch('/lotes/{item}/regularizar', [ComercialController::class, 'regularizar'])->name('lotes.regularizar');
        Route::get('/ajustes-inventario', [AjusteInventarioController::class, 'index'])->name('ajustes-inventario.index');
        Route::get('/ajustes-inventario/create', [AjusteInventarioController::class, 'create'])->name('ajustes-inventario.create');
        Route::post('/ajustes-inventario', [AjusteInventarioController::class, 'store'])->name('ajustes-inventario.store');
        Route::get('/ajustes-inventario/{ajusteInventario}/edit', [AjusteInventarioController::class, 'edit'])->name('ajustes-inventario.edit');
        Route::put('/ajustes-inventario/{ajusteInventario}', [AjusteInventarioController::class, 'update'])->name('ajustes-inventario.update');
        Route::get('/ajustes-inventario/{ajusteInventario}', [AjusteInventarioController::class, 'show'])->name('ajustes-inventario.show');
        Route::patch('/ajustes-inventario/{ajusteInventario}/anular', [AjusteInventarioController::class, 'anular'])->name('ajustes-inventario.anular');
        Route::get('/descartes-productos/reporte.pdf', [DescarteProductoController::class, 'consolidadoPdf'])->name('descartes-productos.reporte.pdf');
        Route::get('/descartes-productos/reporte.csv', [DescarteProductoController::class, 'csv'])->name('descartes-productos.csv');
        Route::get('/descartes-productos/{descarteProducto}/pdf', [DescarteProductoController::class, 'pdf'])->name('descartes-productos.pdf');
        Route::patch('/descartes-productos/{descarteProducto}/procesar', [DescarteProductoController::class, 'procesar'])->name('descartes-productos.procesar');
        Route::resource('descartes-productos', DescarteProductoController::class)->parameters(['descartes-productos' => 'descarteProducto']);

        Route::get('/reportes-comerciales', [ReporteComercialController::class, 'index'])->name('reportes-comerciales.index');
        Route::get('/reportes-comerciales/productos.csv', [ReporteComercialController::class, 'productosCsv'])->name('reportes-comerciales.productos.csv');
        Route::get('/reportes-comerciales/ventas/{ticket}', [ReporteComercialController::class, 'venta'])->name('reportes-comerciales.venta');
        Route::get('/reportes-comerciales/pdf', [ReporteComercialController::class, 'pdf'])->name('reportes-comerciales.pdf');
        Route::get('/reportes-comerciales/excel', [ReporteComercialController::class, 'excel'])->name('reportes-comerciales.excel');
        Route::get('/reportes-ingresos', [ReporteComercialController::class, 'ingresosIndex'])->name('reportes-ingresos.index');

        Route::redirect('/consignaciones', '/ingresos-productores');
        Route::redirect('/mis-consignaciones', '/ingresos-productores');
        Route::get('/consignaciones/{id}', fn ($id) => redirect('/ingresos-productores/'.$id))->whereNumber('id');
        Route::redirect('/reportes-inventario', '/reportes-comerciales');
    });

});
