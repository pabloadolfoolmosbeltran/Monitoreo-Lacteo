<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produccion extends Model
{
    use HasFactory;

    public const ETAPA_PASTEURIZACION = 'Pasteurizacion';
    public const ETAPA_PRODUCCION = 'Produccion';
    public const ETAPA_FINALIZADA = 'Finalizada';

    // APUNTE:
    // Produccion representa un lote elaborado. Desde este modelo se navega hacia
    // su responsable, producto, dispositivo, lecturas de temperatura, alertas y bitácora.
    protected $table = 'producciones';

    protected $fillable = [
        'user_id',
        'producto_id',
        'dispositivo_id',
        'cantidad_leche',
        'tipo_cuajo',
        'cantidad_cuajo',
        'temperatura_objetivo',
        'temperatura_inicial',
        'temperatura_final',
        'temperatura_minima',
        'temperatura_maxima',
        'temperatura_promedio',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'etapa',
        'observaciones'
    ];

    protected $casts = [
        'cantidad_leche' => 'decimal:2',
        'cantidad_cuajo' => 'decimal:2',
        'temperatura_objetivo' => 'decimal:2',
        'temperatura_inicial' => 'decimal:2',
        'temperatura_final' => 'decimal:2',
        'temperatura_minima' => 'decimal:2',
        'temperatura_maxima' => 'decimal:2',
        'temperatura_promedio' => 'decimal:2',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function dispositivo(): BelongsTo
    {
        return $this->belongsTo(Dispositivo::class);
    }

    public function lecturas(): HasMany
    {
        return $this->hasMany(Lectura::class);
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class);
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class);
    }

    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class);
    }
}
