<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'temperatura_minima',
        'temperatura_maxima',
        'temperatura_pasteurizacion', // <--- NUEVO
        'activo'
    ];

    // Esto asegura que Laravel siempre vea estos campos como números
    protected $casts = [
        'temperatura_minima' => 'float',
        'temperatura_maxima' => 'float',
        'temperatura_pasteurizacion' => 'float',
    ];

    /**
     * Un producto tiene muchas producciones
     */
    public function producciones(): HasMany
    {
        return $this->hasMany(Produccion::class);
    }
}
