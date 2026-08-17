<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Mostrar todos los productos.
     */
    /**
 * Mostrar todos los productos.
 */
public function index(Request $request)
{
    $consulta = Producto::query();

    /*
    |--------------------------------------------------------------------------
    | Buscar por nombre
    |--------------------------------------------------------------------------
    */

    if ($request->filled('buscar')) {

        $consulta->where(
            'nombre',
            'like',
            '%' . $request->buscar . '%'
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Ordenar y paginar
    |--------------------------------------------------------------------------
    */

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
     * Mostrar formulario de creación.
     */
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
    /**
 * Guardar un nuevo producto.
 */
public function store(Request $request)
{
    $request->validate([

        'nombre' => 'required|string|max:100|unique:productos,nombre',

        'temperatura_minima' => 'required|numeric',

        'temperatura_maxima' => 'required|numeric|gt:temperatura_minima',
        'temperatura_pasteurizacion' => 'required|numeric', // <--- AGREGAR

        'descripcion' => 'nullable|string',

    ]);

    Producto::create([

        'nombre' => $request->nombre,

        'temperatura_minima' => $request->temperatura_minima,

        'temperatura_maxima' => $request->temperatura_maxima,

        'descripcion' => $request->descripcion,

        'temperatura_pasteurizacion' => $request->temperatura_pasteurizacion, // <--- GUARDAR

        'activo' => $request->has('activo'),

    ]);

    return redirect('/productos')
        ->with('success', 'Producto registrado correctamente.');
}

    /**
     * Mostrar formulario de edición.
     */
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
     * Actualizar producto.
     */
    /**
 * Actualizar un producto.
 */
public function update(Request $request, Producto $producto)
{
    $request->validate([

        'nombre' => 'required|string|max:100|unique:productos,nombre,' . $producto->id,

        'temperatura_minima' => 'required|numeric',

        'temperatura_maxima' => 'required|numeric|gt:temperatura_minima',

        'temperatura_pasteurizacion' => 'required|numeric', // <--- AGREGAR

        'descripcion' => 'nullable|string',

    ]);

    $producto->update([

        'nombre' => $request->nombre,

        'descripcion' => $request->descripcion,

        'temperatura_minima' => $request->temperatura_minima,

        'temperatura_maxima' => $request->temperatura_maxima,

        'temperatura_pasteurizacion' => $request->temperatura_pasteurizacion, // <--- GUARDAR

        'activo' => $request->has('activo'),

    ]);

    return redirect('/productos')
        ->with('success', 'Producto actualizado correctamente.');
}

    /**
     * Eliminar producto.
     */
    /**
 * Eliminar un producto.
 */
public function destroy(Producto $producto)
{
    /*
    |--------------------------------------------------------------------------
    | Verificar si el producto está siendo utilizado
    |--------------------------------------------------------------------------
    */

    if ($producto->producciones()->exists()) {

        return redirect('/productos')
            ->with(
                'error',
                'No se puede eliminar el producto porque tiene producciones registradas.'
            );

    }

    $producto->delete();

    return redirect('/productos')
        ->with(
            'success',
            'Producto eliminado correctamente.'
        );
}
}
