<?php

namespace App\Models;

use App\Support\Decimal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'ingreso_productor_item_id',
        'user_id',
        'ticket_venta_id',
        'cantidad_vendida',
        'precio_unitario_venta',
        'fecha_venta',
        'observaciones',
    ];

    protected $casts = [
        'cantidad_vendida' => 'integer',
        'precio_unitario_venta' => 'decimal:2',
        'fecha_venta' => 'datetime',
    ];

    protected $appends = [
        'total_venta',
        'margen_obtenido',
    ];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(IngresoProductorItem::class, 'ingreso_productor_item_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(TicketVenta::class, 'ticket_venta_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTotalVentaAttribute(): string
    {
        return Decimal::mul($this->cantidad_vendida, $this->precio_unitario_venta, 2);
    }

    public function getMargenObtenidoAttribute(): string
    {
        $this->loadMissing('lote');

        $margenUnitario = Decimal::sub(
            $this->precio_unitario_venta,
            $this->lote->precio_acopio_unitario,
            2
        );

        return Decimal::mul($this->cantidad_vendida, $margenUnitario, 2);
    }
}
