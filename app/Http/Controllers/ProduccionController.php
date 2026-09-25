<?php

namespace App\Http\Controllers;

use App\Models\Dispositivo;
use App\Models\Produccion;
use App\Models\Producto;
use App\Models\User;
use App\Services\ActuadorService;
use App\Services\EventoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProduccionController extends Controller
{
    public function __construct(private readonly ActuadorService $actuadores) {}

    public function index()
    {
        $usuario = Auth::user();

        if ($usuario && $usuario->rol === 'Administrador') {
            $users = User::where('activo', true)->whereIn('rol', ['Administrador', 'Trabajador'])->orderBy('name')->get();
        } else {
            $users = $usuario ? collect([$usuario]) : collect();
        }

        return view('produccion.index', [
            'productos' => Producto::where('activo', true)->get(),
            'users' => $users,
            'produccionActiva' => Produccion::where('estado', 'En proceso')->first(),
            'usuario' => $usuario,
        ]);
    }

    public function iniciar(Request $request)
    {
        $datos = $this->validarInicio($request);
        $usuario = Auth::user();

        if ($usuario && $usuario->rol !== 'Administrador') {
            $datos['user_id'] = $usuario->id;
        }

        if (Produccion::where('estado', 'En proceso')->exists()) {
            return redirect()->back()->with('error', 'Ya existe una producción en proceso.');
        }

        $dispositivo = Dispositivo::first();
        if (! $dispositivo) {
            return redirect()->back()->with('error', 'No existe un dispositivo ESP32 registrado.');
        }

        $detalleInsumo = '';

        $produccion = DB::transaction(function () use ($datos, $dispositivo, &$detalleInsumo) {
            $producto = Producto::findOrFail($datos['producto_id']);

            $insumoTotalRequerido = $this->calcularInsumoRequerido(
                $producto,
                (float) $datos['cantidad_leche']
            );

            $detalleInsumo = $this->detalleInsumo($producto, $insumoTotalRequerido);

            return Produccion::create([
                'user_id' => $datos['user_id'],
                'producto_id' => $datos['producto_id'],
                'dispositivo_id' => $dispositivo->id,
                'cantidad_leche' => $datos['cantidad_leche'],
                'tipo_cuajo' => $producto->tipo_cuajo ?? 'N/A',
                'cantidad_cuajo' => $insumoTotalRequerido,
                'temperatura_objetivo' => $datos['temperatura_objetivo'],
                'fecha_inicio' => now(),
                'estado' => 'En proceso',
                'etapa' => 'Produccion',
                'observaciones' => $datos['observaciones'] ?? null,
            ]);
        });

        EventoService::registrar(
            $produccion->id,
            'Producción',
            'Producción iniciada. Etapa actual: Producción.'.$detalleInsumo
        );

        $this->actuadores->encenderSiEstaEnAutomatico(ActuadorService::MOTOR, $produccion->id);
        $this->actuadores->encenderSiEstaEnAutomatico(ActuadorService::VENTILADOR, $produccion->id);

        return redirect('/produccion')
            ->with('success', 'Producción iniciada correctamente y registrada en bitácora.');
    }

    public function finalizar()
    {
        $produccion = Produccion::where('estado', 'En proceso')->first();

        if (! $produccion) {
            return redirect()->back()->with('error', 'No existe una producción activa.');
        }

        $produccion->update([
            'estado' => 'Finalizada',
            'fecha_fin' => now(),
        ]);

        Cache::forget("prod_count_{$produccion->id}");
        Cache::forget("prod_sum_{$produccion->id}");
        Cache::forget("chart_produccion_{$produccion->id}");

        EventoService::registrar($produccion->id, 'Producción', 'La producción fue finalizada correctamente.');

        $this->actuadores->apagarAlFinalizar(ActuadorService::MOTOR, $produccion->id);
        $this->actuadores->apagarAlFinalizar(ActuadorService::VENTILADOR, $produccion->id);

        return redirect('/produccion')
            ->with('success', 'Producción finalizada correctamente y registrada en bitácora.');
    }

    // APUNTE:
    // Estas reglas protegen el inicio del lote antes de tocar stock o crear
    // registros. La ruta recibe datos del formulario de produccion/index.blade.php.
    private function validarInicio(Request $request): array
    {
        return $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('activo', true)->whereIn('rol', ['Administrador', 'Trabajador'])),
            ],
            'producto_id' => [
                'required',
                Rule::exists('productos', 'id')->where('activo', true),
            ],
            'cantidad_leche' => 'required|numeric|min:1|max:999999.99',
            'temperatura_objetivo' => 'required|numeric|min:1|max:150',
            'observaciones' => 'nullable|string|max:1000',
        ]);
    }

    private function calcularInsumoRequerido(Producto $producto, float $cantidadLeche): float
    {
        return round($cantidadLeche * (float) ($producto->cuajo_por_litro ?? 0), 2);
    }

    private function detalleInsumo(Producto $producto, float $insumoTotalRequerido): string
    {
        if ($insumoTotalRequerido <= 0) {
            return '';
        }

        $unidad = $producto->unidad_cuajo ?? 'ml';

        return " Recomendación de insumo: {$insumoTotalRequerido} {$unidad}.";
    }
}
