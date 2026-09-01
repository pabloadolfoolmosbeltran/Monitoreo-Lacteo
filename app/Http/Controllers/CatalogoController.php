<?php

namespace App\Http\Controllers;

use App\Models\Presentacion;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $query = Presentacion::with('producto')
            ->where('activo', true)
            ->whereHas('producto', fn ($query) => $query->where('activo', true));

        // Buscador por término (nombre de presentación, sabor, envase o nombre del producto)
        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%")
                  ->orWhere('sabor', 'LIKE', "%{$buscar}%")
                  ->orWhere('envase', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('producto', function ($qp) use ($buscar) {
                      $qp->where('nombre', 'LIKE', "%{$buscar}%");
                  });
            });
        }

        // Filtro por Tipo de Producto
        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->input('producto_id'));
        }

        // Filtro por Con / Sin fruta (1 o 0)
        if ($request->filled('con_fruta')) {
            $query->where('con_fruta', $request->input('con_fruta'));
        }

        $presentaciones = $query->orderBy('nombre')->get();

        // Se obtienen los productos activos para llenar el selector del filtro
        $productos = Producto::where('activo', true)->orderBy('nombre')->get();

        return view('catalogo.catalogo', compact('presentaciones', 'productos'));
    }

    public function detalle($presentacion)
    {
        $presentacion = Presentacion::with('producto')
            ->where('activo', true)
            ->whereHas('producto', fn ($query) => $query->where('activo', true))
            ->findOrFail($presentacion);

        return view('catalogo.detalle', compact('presentacion'));
    }

    public function nosotros()
    {
        return view('catalogo.nosotros');
    }

    // Funcion Para Contacto
    public function contacto()
    {
        $productores = User::where('activo', true)
            ->where('rol', 'Administrador')
            ->whereNotNull('nombre_unidad_productiva')
            ->whereNotNull('direccion')
            ->orderBy('nombre_unidad_productiva')
            ->get(['name', 'telefono', 'direccion', 'nombre_unidad_productiva']);

        return view('catalogo.contacto', compact('productores'));
    }
}