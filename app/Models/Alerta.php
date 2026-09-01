<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerta extends Model
{
    use HasFactory;

    protected $table = 'alertas';

    protected $fillable = [
        'produccion_id',
        'lectura_id',
        'tipo',
        'mensaje',
        'atendida'
    ];

    protected $casts = [
        'atendida' => 'boolean'
    ];

    public function produccion(): BelongsTo
    {
        return $this->belongsTo(Produccion::class);
    }

    public function lectura(): BelongsTo
    {
        return $this->belongsTo(Lectura::class);
    }
}
