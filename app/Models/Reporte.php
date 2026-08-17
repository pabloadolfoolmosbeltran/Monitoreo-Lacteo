<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reporte extends Model
{
    use HasFactory;

    protected $table = 'reportes';

    protected $fillable = [
        'produccion_id',
        'nombre_archivo',
        'ruta_archivo',
        'fecha_generacion'
    ];

    protected $casts = [
        'fecha_generacion' => 'datetime'
    ];

    /**
     * El reporte pertenece a una producción.
     */
    public function produccion(): BelongsTo
    {
        return $this->belongsTo(Produccion::class);
    }
}
