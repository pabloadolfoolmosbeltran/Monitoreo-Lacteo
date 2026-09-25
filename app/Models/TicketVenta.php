<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class TicketVenta extends Model
{
    protected $table = 'tickets_venta';
    protected $fillable = ['clave','payload_hash','user_id','metodo_pago','cliente','total'];
    protected $casts = ['total'=>'decimal:2'];
    public function ventas(): HasMany { return $this->hasMany(Venta::class,'ticket_venta_id'); }
    public function vendedor(): BelongsTo { return $this->belongsTo(User::class,'user_id'); }
}

