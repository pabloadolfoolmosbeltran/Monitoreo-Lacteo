<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarAjusteInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ajuste = $this->route('ajusteInventario');

        return $ajuste && ($this->user()?->can('update', $ajuste) ?? false);
    }

    public function rules(): array
    {
        return [
            'cantidad_nueva' => 'required|integer|min:0|max:999999',
            'tipo_motivo' => 'required|in:conteo,daño,remanente,otro',
            'motivo' => 'required|string|min:3|max:1000',
        ];
    }
}
