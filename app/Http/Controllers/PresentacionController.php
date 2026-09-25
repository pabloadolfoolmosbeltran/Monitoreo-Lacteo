<?php

namespace App\Http\Controllers;

use App\Models\Presentacion;
use App\Models\Producto;
use App\Services\BusquedaPresentacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PresentacionController extends Controller
{
    private const IMAGE_DIRECTORY = 'presentaciones';

    public function __construct(private BusquedaPresentacion $busqueda) {}

    public function index(Request $request)
    {
        $datos = $request->validate([
            'q' => 'nullable|string|max:100',
            'estado' => 'nullable|in:eliminados',
        ]);
        $busqueda = trim($datos['q'] ?? '');
        $eliminados = ($datos['estado'] ?? null) === 'eliminados';
        abort_if($eliminados && $request->user()?->rol !== 'Administrador', 403);

        $consulta = Presentacion::with('producto')->where('activo', ! $eliminados);

        if (! $eliminados) {
            $consulta->whereHas('producto', fn ($query) => $query->where('activo', true));
        }

        $this->busqueda->aplicar($consulta, $busqueda);

        $presentaciones = $consulta
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('presentaciones.index', compact('presentaciones', 'busqueda', 'eliminados'));
    }

    public function create()
    {
        $productos = Producto::where('activo', true)->orderBy('nombre')->get();

        return view('presentaciones.create', compact('productos'));
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $datos['con_fruta'] = $request->boolean('con_fruta');
        $datos['imagen_comercial'] = $request->file('imagen_comercial')?->store(self::IMAGE_DIRECTORY, 'public');
        $datos['activo'] = true;
        $datos['stock'] = 0;

        Presentacion::create($datos);

        return redirect()->route('presentaciones.index')->with('success', 'Presentación registrada correctamente.');
    }

    public function edit(Presentacion $presentacion)
    {
        $productos = Producto::where('activo', true)->orderBy('nombre')->get();

        return view('presentaciones.edit', compact('presentacion', 'productos'));
    }

    public function update(Request $request, Presentacion $presentacion)
    {
        $datos = $this->validar($request);
        $datos['con_fruta'] = $request->boolean('con_fruta');
        $datos['imagen_comercial'] = $presentacion->imagen_comercial;

        if ($request->hasFile('imagen_comercial')) {
            if ($presentacion->imagen_comercial) {
                Storage::disk('public')->delete($presentacion->imagen_comercial);
            }

            $datos['imagen_comercial'] = $request->file('imagen_comercial')->store(self::IMAGE_DIRECTORY, 'public');
        }

        $presentacion->update($datos);

        return redirect()->route('presentaciones.index')->with('success', 'Presentación actualizada correctamente.');
    }

    public function destroy(Presentacion $presentacion)
    {
        // Eliminación lógica: la imagen también se conserva por si se reactiva.
        $presentacion->update(['activo' => false]);

        return redirect()->route('presentaciones.index')->with('success', 'Presentación desactivada correctamente.');
    }

    public function restore(Request $request, Presentacion $presentacion)
    {
        abort_unless($request->user()?->rol === 'Administrador', 403);

        if (! $presentacion->producto?->activo) {
            return redirect()->route('presentaciones.index', ['estado' => 'eliminados'])
                ->with('error', 'Restablezca primero el producto asociado a esta presentación.');
        }

        $presentacion->update(['activo' => true]);

        return redirect()->route('presentaciones.index', ['estado' => 'eliminados'])
            ->with('success', 'Presentación restablecida correctamente.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'producto_id' => 'required|exists:productos,id,activo,1',
            'nombre' => 'required|string|max:255',
            'envase' => 'nullable|string|max:100',
            'sabor' => 'nullable|string|max:100',
            'contenido' => 'nullable|numeric|min:0',
            'unidad' => 'nullable|string|in:ml,L,g,kg',
            'precio' => 'required|numeric|min:0',
            'stock_minimo_alerta' => 'sometimes|integer|min:0|max:999999',
            'imagen_comercial' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);
    }
}
