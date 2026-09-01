<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\User;
use App\Models\Dispositivo;
use App\Models\Produccion;
use App\Models\Lectura;
use App\Models\Alerta;
use App\Models\Actuador;
use App\Models\Sensor;

class DashboardController extends Controller
{
    public function index()
    {
        $motor = Actuador::where('tipo', 'Motor')->first();
        $ventilador = Actuador::where('tipo', 'Ventilador')->first();
        $sensor = Sensor::first();
        $produccion = Produccion::where('estado', 'En proceso')->first();
        $dispositivo = Dispositivo::first();

        $esp32Conectado = false;
        if ($dispositivo && $dispositivo->ultima_conexion) {
            $esp32Conectado = $dispositivo->ultima_conexion->greaterThan(now()->subMinute());
        }

        return view('dashboard.index', [
            'totalProductos' => Producto::where('activo', true)->count(),
            'totalUsuarios' => User::where('activo', true)->count(),
            'totalDispositivos' => Dispositivo::count(),
            'produccionesActivas' => Produccion::where('estado', 'En proceso')->count(),
            'ultimaLectura' => Lectura::latest()->first(),
            'alertasPendientes' => Alerta::where('atendida', false)->count(),
            'motor' => $motor,
            'ventilador' => $ventilador,
            'sensor' => $sensor,
            'produccion' => $produccion,
            'dispositivo' => $dispositivo,
            'esp32Conectado' => $esp32Conectado
        ]);
    }
}
