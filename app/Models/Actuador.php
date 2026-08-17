<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Actuador extends Model
{
    use HasFactory;

    protected $table = 'actuadores';

    protected $fillable = [
        'dispositivo_id',
        'nombre',
        'tipo',
        'estado',
        'modo'
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];

    /**
     * El actuador pertenece a un dispositivo.
     */
    public function dispositivo(): BelongsTo
    {
        return $this->belongsTo(Dispositivo::class);
    }
}
