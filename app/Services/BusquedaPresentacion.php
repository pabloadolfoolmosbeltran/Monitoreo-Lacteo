<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

final class BusquedaPresentacion
{
    public function aplicar(Builder $query, ?string $texto): Builder
    {
        $normalizado = preg_replace('/(?<=\d)(?=\p{L})/u', ' ', str_replace(',', '.', trim((string) $texto)));
        $terminos = preg_split('/\s+/u', $normalizado, -1, PREG_SPLIT_NO_EMPTY);
        $unidades = [
            'l' => 'L', 'litro' => 'L', 'litros' => 'L',
            'ml' => 'ml', 'mililitro' => 'ml', 'mililitros' => 'ml',
            'g' => 'g', 'gramo' => 'g', 'gramos' => 'g',
            'kg' => 'kg', 'kilo' => 'kg', 'kilos' => 'kg',
            'kilogramo' => 'kg', 'kilogramos' => 'kg',
        ];

        foreach ($terminos as $termino) {
            $clave = mb_strtolower($termino);
            if (isset($unidades[$clave])) {
                $query->whereRaw('LOWER(unidad) = ?', [mb_strtolower($unidades[$clave])]);
            } elseif (is_numeric($termino)) {
                $query->where('contenido', (float) $termino);
            } else {
                $query->where(function (Builder $q) use ($termino) {
                    $coincidencia = '%'.$termino.'%';
                    $q->where('nombre', 'like', $coincidencia)
                        ->orWhere('sabor', 'like', $coincidencia)
                        ->orWhere('envase', 'like', $coincidencia)
                        ->orWhereHas('producto', fn (Builder $producto) => $producto->where('nombre', 'like', $coincidencia));
                });
            }
        }

        return $query;
    }
}
