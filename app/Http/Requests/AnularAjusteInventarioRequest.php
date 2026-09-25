<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnularAjusteInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('delete', $this->route('ajusteInventario')) ?? false;
    }

    public function rules(): array
    {
        return ['motivo_anulacion' => 'required|string|min:3|max:1000'];
    }
}
