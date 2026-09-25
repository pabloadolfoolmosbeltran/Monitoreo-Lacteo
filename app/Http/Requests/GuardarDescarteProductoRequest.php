<?php

namespace App\Http\Requests;

use App\Models\DescarteProducto;
use Illuminate\Foundation\Http\FormRequest;

class GuardarDescarteProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $descarte = $this->route('descarteProducto');

        return $descarte ? $this->user()->can('update', $descarte) : $this->user()->can('create', DescarteProducto::class);
    }

    public function rules(): array
    {
        return ['lote_id' => 'required|integer|exists:ingresos_productores_items,id', 'cantidad' => 'required|integer|min:1|max:999999',
            'tipo_motivo' => 'required|in:caducado,dañado,deterioro,otro', 'notas' => 'nullable|string|max:2000'];
    }
}
