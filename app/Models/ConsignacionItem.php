<?php

namespace App\Models;

use App\Support\Decimal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsignacionItem extends Model
{
    use HasFactory;

    protected $table = 'consignacion_items';

    protected $fillable = [
        'consignacion_id',
        'presentacion_id',
        'cantidad_recibida',
        'cantidad_disponible',
        'precio_productor',
        'precio_venta',
    ];

    protected $casts = [
        'cantidad_recibida' => 'decimal:3',
        'cantidad_disponible' => 'decimal:3',
        'precio_productor' => 'decimal:2',
        'precio_venta' => 'decimal:2',
    ];

    protected $appends = [
        'margen_unitario',
    ];

    public function consignacion(): BelongsTo
    {
        return $this->belongsTo(Consignacion::class);
    }

    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(Presentacion::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function devoluciones(): HasMany
    {
        return $this->hasMany(Devolucion::class);
    }

    public function getMargenUnitarioAttribute(): string
    {
        return Decimal::sub($this->precio_venta, $this->precio_productor, 2);
    }

    public function getCantidadVendidaAttribute(): string
    {
        $ventas = $this->relationLoaded('ventas') ? $this->ventas : $this->ventas()->get();

        return $ventas->reduce(
            fn (string $total, Venta $venta): string => Decimal::add($total, $venta->cantidad_vendida, 3),
            '0.000'
        );
    }

    public function getMontoProductorAttribute(): string
    {
        return Decimal::mul($this->cantidad_vendida, $this->precio_productor, 2);
    }

    public function getMargenEncargadoAttribute(): string
    {
        $ventas = $this->relationLoaded('ventas') ? $this->ventas : $this->ventas()->get();

        return $ventas->reduce(
            fn (string $total, Venta $venta): string => Decimal::add($total, $venta->margen_obtenido, 2),
            '0.00'
        );
    }
}
