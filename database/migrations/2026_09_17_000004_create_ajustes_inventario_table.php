<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ajustes_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('ingresos_productores_items')->restrictOnDelete();
            $table->foreignId('creado_por')->constrained('users')->restrictOnDelete();
            $table->foreignId('actualizado_por')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('cantidad_anterior');
            $table->unsignedInteger('cantidad_nueva');
            $table->integer('diferencia');
            $table->string('tipo_motivo', 40);
            $table->text('motivo');
            $table->string('estado', 20)->default('activo');
            $table->foreignId('anulado_por')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('anulado_en')->nullable();
            $table->text('motivo_anulacion')->nullable();
            $table->timestamps();

            $table->index(['lote_id', 'estado'], 'ajustes_lote_estado_idx');
            $table->index(['tipo_motivo', 'created_at'], 'ajustes_motivo_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ajustes_inventario');
    }
};
