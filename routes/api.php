<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Esp32Controller;

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/estado', [Esp32Controller::class, 'estado']);
    Route::post('/temperatura', [Esp32Controller::class, 'temperatura']);
    Route::get('/dashboard', [Esp32Controller::class, 'dashboard']);
    Route::post('/ping', [Esp32Controller::class, 'ping']);
    Route::get('/temperaturas', [Esp32Controller::class, 'temperaturas']);
    Route::post('/alerta-pasteurizacion', [Esp32Controller::class, 'alertaPasteurizacion']);
    Route::post('/finalizar-produccion', [Esp32Controller::class, 'finalizarProduccionAutomatica']);
    Route::post('/pasteurizacion', [Esp32Controller::class, 'pasteurizacion']);
});
