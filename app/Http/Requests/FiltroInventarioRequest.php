<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FiltroInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'producto' => 'nullable|string|max:100', 'productor' => 'nullable|string|max:100',
            'ingreso_desde' => 'nullable|date', 'ingreso_hasta' => 'nullable|date|after_or_equal:ingreso_desde',
            'vence_desde' => 'nullable|date', 'vence_hasta' => 'nullable|date|after_or_equal:vence_desde',
            'detalle_presentacion' => 'nullable|integer|exists:presentaciones,id',
            'detalle_productor' => 'nullable|integer|exists:productores,id',
        ];
    }

    public function messages(): array
    {
        return [
            'ingreso_hasta.after_or_equal' => 'La fecha final de ingreso no puede ser anterior a la inicial.',
            'vence_hasta.after_or_equal' => 'La fecha final de vencimiento no puede ser anterior a la inicial.',
        ];
    }
}
