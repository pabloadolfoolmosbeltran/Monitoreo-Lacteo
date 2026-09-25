<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    private const IMAGE_DIRECTORY = 'productos';

    /**
     * Mostrar todos los productos.
     */
    public function index(Request $request)
    {
        $request->validate([
            'buscar' => 'nullable|string|max:100',
            'estado' => 'nullable|in:eliminados',
        ]);

        $eliminados = $request->input('estado') === 'eliminados';
        abort_if($eliminados && $request->user()?->rol !== 'Administrador', 403);

        $consulta = Producto::where('activo', ! $eliminados);

        if ($request->filled('buscar')) {
            $consulta->where(
                'nombre',
                'like',
                '%'.$request->buscar.'%'
            );
        }

        $productos = $consulta
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view(
            'productos.index',
            compact('productos', 'eliminados')
        );
    }

    /**
     * Mostrar formulario para crear un producto.
     */
    public function create()
    {
        return view('productos.create');
    }

    /**
     * Guardar un nuevo producto.
     */
    public function store(Request $request)
    {
        $datos = $this->validarProducto($request);
        $datos['imagen_referencial'] = $this->guardarImagenReferencial($request);
        $datos['activo'] = $request->boolean('activo');

        Producto::create($datos);

        return redirect('/productos')
            ->with('success', 'Producto registrado correctamente.');
    }

    /**
     * Mostrar formulario para editar un producto.
     */
    public function edit(Producto $producto)
    {
        return view(
            'productos.edit',
            compact('producto')
        );
    }

    /**
     * Actualizar un producto.
     */
    public function update(Request $request, Producto $producto)
    {
        $datos = $this->validarProducto($request, $producto);
        $datos['imagen_referencial'] = $this->guardarImagenReferencial($request, $producto);
        $datos['activo'] = $request->boolean('activo');

        $producto->update($datos);

        return redirect('/productos')
            ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Eliminar un producto.
     */
    public function destroy(Producto $producto)
    {
        // Eliminación lógica: se conserva el registro, sus relaciones y su imagen.
        $producto->update(['activo' => false]);

        return redirect('/productos')
            ->with(
                'success',
                'Producto desactivado correctamente.'
            );
    }

    public function restore(Request $request, Producto $producto)
    {
        abort_unless($request->user()?->rol === 'Administrador', 403);

        $producto->update(['activo' => true]);

        return redirect('/productos?estado=eliminados')
            ->with('success', 'Producto restablecido correctamente.');
    }

    // APUNTE:
    // Store y update comparten las mismas reglas. Centralizarlas evita que un
    // campo se valide distinto al crear y al editar el producto.
    private function validarProducto(Request $request, ?Producto $producto = null): array
    {
        $nombreUnico = Rule::unique('productos', 'nombre');

        if ($producto) {
            $nombreUnico->ignore($producto);
        }

        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                $nombreUnico,
                'regex:/^[a-zA-ZÀ-ÿñÑ0-9\s]+$/',
            ],
            'tipo_cuajo' => 'nullable|string|max:100|regex:/^[a-zA-ZÀ-ÿñÑ0-9\s\.\,\-]+$/',
            'imagen_referencial' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'unidad_cuajo' => 'nullable|string|in:ml,g,kg,gotas,pastilla',
            'cuajo_por_litro' => 'nullable|numeric|min:0',
            'temperatura_minima' => 'required|numeric',
            'temperatura_maxima' => 'required|numeric|gt:temperatura_minima',
            'temperatura_pasteurizacion' => 'required|numeric',
            'descripcion' => 'nullable|string|regex:/^[a-zA-ZÀ-ÿñÑ0-9\s\.\,\-]*$/',
        ], [
            'nombre.regex' => 'El nombre no puede contener caracteres especiales.',
            'descripcion.regex' => 'La descripción contiene caracteres no permitidos.',
        ]);

        $datos['unidad_cuajo'] = $datos['unidad_cuajo'] ?? 'ml';

        return $datos;
    }

    private function guardarImagenReferencial(Request $request, ?Producto $producto = null): ?string
    {
        if (! $request->hasFile('imagen_referencial')) {
            return $producto?->imagen_referencial;
        }

        if ($producto?->imagen_referencial && Storage::disk('public')->exists($producto->imagen_referencial)) {
            Storage::disk('public')->delete($producto->imagen_referencial);
        }

        return $request->file('imagen_referencial')->store(self::IMAGE_DIRECTORY, 'public');
    }
}
