<?php

namespace App\Models;

use App\Support\Decimal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Consignacion extends Model
{
    use HasFactory;

    protected $table = 'consignaciones';

    protected $fillable = [
        'user_id',
        'fecha_entrada',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'fecha_entrada' => 'date',
    ];

    public function productor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ConsignacionItem::class);
    }

    public function liquidacion(): HasOne
    {
        return $this->hasOne(Liquidacion::class);
    }

    public function getMontoProductorAttribute(): string
    {
        return $this->sumarItems('monto_productor');
    }

    public function getMargenEncargadoAttribute(): string
    {
        return $this->sumarItems('margen_encargado');
    }

    private function sumarItems(string $attribute): string
    {
