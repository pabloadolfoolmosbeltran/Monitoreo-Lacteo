<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evento extends Model
{
    protected $table = 'eventos';

    protected $fillable = [
        'produccion_id',
        'user_id',
        'tipo',
        'descripcion',
        'fecha_hora'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime'
    ];

    // Añadimos el tipo de retorno BelongsTo para mantener la consistencia
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function produccion(): BelongsTo
    {
        return $this->belongsTo(Produccion::class);
    }
}
