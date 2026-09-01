<?php

namespace App\Http\Controllers;

use App\Models\Presentacion;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PresentacionController extends Controller
{
    public function index()
    {
        $presentaciones = Presentacion::with('producto')
            ->where('activo', true)
            ->whereHas('producto', fn ($query) => $query->where('activo', true))
            ->orderBy('nombre')
            ->paginate(12);

        return view('presentaciones.index', compact('presentaciones'));
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
        $datos['imagen_comercial'] = $request->file('imagen_comercial')?->store('presentaciones', 'public');
        $datos['activo'] = true;

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

            $datos['imagen_comercial'] = $request->file('imagen_comercial')->store('presentaciones', 'public');
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

    private function validar(Request $request): array
    {
        return $request->validate([
            'producto_id'      => 'required|exists:productos,id,activo,1',
            'nombre'           => 'required|string|max:255',
            'envase'           => 'nullable|string|max:100',
            'sabor'            => 'nullable|string|max:100',
            'contenido'        => 'nullable|numeric|min:0',
            'unidad'           => 'nullable|string|in:ml,L,g,kg',
            'precio'           => 'required|numeric|min:0',
            'stock'            => 'required|integer|min:0',
            'imagen_comercial' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);
    }
}