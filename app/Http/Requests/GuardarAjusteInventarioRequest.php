<?php

namespace App\Http\Requests;

use App\Models\AjusteInventario;
use Illuminate\Foundation\Http\FormRequest;

class GuardarAjusteInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', AjusteInventario::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'lote_id' => 'required|integer|exists:ingresos_productores_items,id',
            'cantidad_nueva' => 'required|integer|min:0|max:999999',
            'tipo_motivo' => 'required|in:conteo,daño,remanente,otro',
            'motivo' => 'required|string|min:3|max:1000',
        ];
    }
}
