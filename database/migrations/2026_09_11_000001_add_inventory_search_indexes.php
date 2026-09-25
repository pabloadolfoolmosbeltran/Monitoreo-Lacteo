<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingresos_productores', fn (Blueprint $t) => $t->index(['productor_id', 'estado', 'fecha_ingreso'], 'ingresos_productor_estado_fecha_idx'));
        Schema::table('ingresos_productores_items', function (Blueprint $t) {
            $t->index(['presentacion_id', 'fecha_caducidad', 'cantidad_disponible'], 'lotes_presentacion_vencimiento_saldo_idx');
            $t->index('fecha_recepcion', 'lotes_recepcion_idx');
        });
        Schema::table('productores', fn (Blueprint $t) => $t->index('activo', 'productores_activo_idx'));
    }

    public function down(): void
    {
        Schema::table('ingresos_productores', fn (Blueprint $t) => $t->dropIndex('ingresos_productor_estado_fecha_idx'));
        Schema::table('ingresos_productores_items', function (Blueprint $t) {
            $t->dropIndex('lotes_presentacion_vencimiento_saldo_idx');
            $t->dropIndex('lotes_recepcion_idx');
        });
        Schema::table('productores', fn (Blueprint $t) => $t->dropIndex('productores_activo_idx'));
    }
};
