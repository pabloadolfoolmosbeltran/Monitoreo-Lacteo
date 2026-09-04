<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    /**
     * Mostrar todos los productos.
     */
    public function index(Request $request)
    {
        $consulta = Producto::where('activo', true);

        if ($request->filled('buscar')) {
            $consulta->where(
                'nombre',
                'like',
                '%' . $request->buscar . '%'
            );
        }

        $productos = $consulta
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view(
            'productos.index',
            compact('productos')
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
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                'unique:productos,nombre',
                'regex:/^[a-zA-ZÀ-ÿñÑ0-9\s]+$/',
            ],
            'tipo_cuajo' => 'nullable|string|max:100|regex:/^[a-zA-ZÀ-ÿñÑ0-9\s\.\,\-]+$/',
            'imagen_referencial' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'unidad_cuajo' => 'nullable|string|in:ml,g,pastilla,gotas',
            'stock_cuajo' => 'nullable|numeric|min:0',
            'cuajo_por_litro' => 'nullable|numeric|min:0',
            'temperatura_minima' => 'required|numeric',
            'temperatura_maxima' => 'required|numeric|gt:temperatura_minima',
            'temperatura_pasteurizacion' => 'required|numeric',
            'instrucciones' => 'nullable|string',
            'descripcion' => 'nullable|string|regex:/^[a-zA-ZÀ-ÿñÑ0-9\s\.\,\-]*$/',
        ], [
            'nombre.regex' => 'El nombre no puede contener caracteres especiales.',
            'descripcion.regex' => 'La descripción contiene caracteres no permitidos.',
        ]);

        // Procesar imagen referencial si existe
        $imagenPath = null;
        if ($request->hasFile('imagen_referencial')) {
            $imagenPath = $request->file('imagen_referencial')->store('productos', 'public');
        }

        Producto::create([
            'nombre' => $request->nombre,
            'tipo_cuajo' => $request->tipo_cuajo,
            'imagen_referencial' => $imagenPath,
            'cuajo_por_litro' => $request->cuajo_por_litro,
            'unidad_cuajo' => $request->unidad_cuajo ?? 'ml',
            'stock_cuajo' => $request->stock_cuajo ?? 0,
            'temperatura_minima' => $request->temperatura_minima,
            'temperatura_maxima' => $request->temperatura_maxima,
            'temperatura_pasteurizacion' => $request->temperatura_pasteurizacion,
            'instrucciones' => $request->instrucciones,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo'),
        ]);

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
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                'unique:productos,nombre,' . $producto->id,
                'regex:/^[a-zA-ZÀ-ÿñÑ0-9\s]+$/',
            ],
            'tipo_cuajo' => 'nullable|string|max:100|regex:/^[a-zA-ZÀ-ÿñÑ0-9\s\.\,\-]+$/',
            'imagen_referencial' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'unidad_cuajo' => 'nullable|string|in:ml,g,pastilla,gotas',
            'stock_cuajo' => 'nullable|numeric|min:0',
            'cuajo_por_litro' => 'nullable|numeric|min:0',
            'temperatura_minima' => 'required|numeric',
            'temperatura_maxima' => 'required|numeric|gt:temperatura_minima',
            'temperatura_pasteurizacion' => 'required|numeric',
            'instrucciones' => 'nullable|string',
            'descripcion' => 'nullable|string|regex:/^[a-zA-ZÀ-ÿñÑ0-9\s\.\,\-]*$/',
        ], [
            'nombre.regex' => 'El nombre no puede contener caracteres especiales.',
            'descripcion.regex' => 'La descripción contiene caracteres no permitidos.',
        ]);

        // Procesar nueva imagen si se sube
        $imagenPath = $producto->imagen_referencial;
        if ($request->hasFile('imagen_referencial')) {
            if ($producto->imagen_referencial && Storage::disk('public')->exists($producto->imagen_referencial)) {
                Storage::disk('public')->delete($producto->imagen_referencial);
            }
            $imagenPath = $request->file('imagen_referencial')->store('productos', 'public');
        }

        $producto->update([
            'nombre' => $request->nombre,
            'tipo_cuajo' => $request->tipo_cuajo,
            'imagen_referencial' => $imagenPath,
            'cuajo_por_litro' => $request->cuajo_por_litro,
            'unidad_cuajo' => $request->unidad_cuajo ?? 'ml',
            'stock_cuajo' => $request->stock_cuajo ?? 0,
            'temperatura_minima' => $request->temperatura_minima,
            'temperatura_maxima' => $request->temperatura_maxima,
            'temperatura_pasteurizacion' => $request->temperatura_pasteurizacion,
            'instrucciones' => $request->instrucciones,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo'),
        ]);

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
}
