<?php

namespace App\Http\Controllers;

use App\Models\Productor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductorController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'buscar' => 'nullable|string|max:100',
            'estado' => 'nullable|in:eliminados',
        ]);

        $eliminados = $request->input('estado') === 'eliminados';
        abort_if($eliminados && $request->user()?->rol !== 'Administrador', 403);

        $consulta = $eliminados
            ? Productor::withTrashed()->where(fn(Builder $q)=>$q->where('activo',false)->orWhereNotNull('deleted_at'))
            : Productor::query()->where('activo',true);
        $consulta->withCount('ingresos');

        if ($request->filled('buscar')) {
            $termino = '%'.trim($request->buscar).'%';
            $consulta->where(function ($query) use ($termino) {
                $query->where('nombres', 'like', $termino)
                    ->orWhere('primer_apellido', 'like', $termino)
                    ->orWhere('segundo_apellido', 'like', $termino)
                    ->orWhere('telefono', 'like', $termino)
                    ->orWhere('nombre_unidad_productiva', 'like', $termino);
            });
        }

        $productores = $consulta->orderBy('nombres')->orderBy('primer_apellido')->paginate(10)->withQueryString();

        return view('productores.index', compact('productores', 'eliminados'));
    }

    public function create()
    {
        return view('productores.create', ['productor' => null]);
    }

    public function store(Request $request)
    {
        $productor = Productor::create($this->datos($request));

        if ($request->expectsJson()) {
            return response()->json(['id' => $productor->id, 'message' => 'Proveedor registrado.'], 201);
        }

        return redirect()->route('productores.index')->with('success', 'Proveedor registrado correctamente.');
    }

    public function show(Productor $productor)
    {
        $productor->loadCount('ingresos')->load([
            'ingresos' => fn ($query) => $query->withCount('items')->latest('id')->limit(10),
        ]);

        return view('productores.show', compact('productor'));
    }

    public function edit(Productor $productor)
    {
        return view('productores.edit', compact('productor'));
    }

    public function update(Request $request, Productor $productor)
    {
        $productor->update($this->datos($request));

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Proveedor actualizado.']);
        }

        return redirect()->route('productores.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Request $request, Productor $productor)
    {
        $productor->update(['activo' => false]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Proveedor archivado; se conserva su historial.']);
        }

        return redirect()->route('productores.index')->with('success', 'Proveedor archivado; su historial se conserva.');
    }

    public function restore(Request $request, int $productor)
    {
        abort_unless($request->user()?->rol === 'Administrador', 403);

        $proveedor = Productor::withTrashed()->findOrFail($productor);
        if ($proveedor->trashed()) $proveedor->restore();
        $proveedor->update(['activo' => true]);

        return redirect()->route('productores.index', ['estado' => 'eliminados'])
            ->with('success', 'Proveedor restablecido correctamente.');
    }

    private function datos(Request $request): array
    {
        $datos = $request->validate([
            'nombres' => 'required|string|max:255',
            'primer_apellido' => 'required|string|max:255',
            'segundo_apellido' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:1000',
            'nombre_unidad_productiva' => 'nullable|string|max:255',
            'activo' => 'sometimes|boolean',
        ]);
        $datos['activo'] = $request->boolean('activo', true);

        return $datos;
    }
}
