<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sensor extends Model
{
    use HasFactory;

    protected $table = 'sensores';

    protected $fillable = [
        'dispositivo_id',
        'nombre',
        'tipo',
        'unidad',
        'estado',
        'numero_serie',
        'temperatura_actual'
    ];

    /**
     * El sensor pertenece a un dispositivo.
     */
    public function dispositivo(): BelongsTo
    {
        return $this->belongsTo(Dispositivo::class);
    }

    /**
     * El sensor tiene muchas lecturas.
     */
    public function lecturas(): HasMany
    {
        return $this->hasMany(Lectura::class);
    }
}
