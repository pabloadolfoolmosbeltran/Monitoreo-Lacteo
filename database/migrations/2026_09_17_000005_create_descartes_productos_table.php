<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('descartes_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('ingresos_productores_items')->restrictOnDelete();
            $table->foreignId('creado_por')->constrained('users')->restrictOnDelete();
            $table->foreignId('actualizado_por')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('cantidad');
            $table->date('fecha_caducidad')->nullable();
            $table->decimal('costo_unitario', 12, 2);
            $table->decimal('perdida_total', 14, 2);
            $table->string('tipo_motivo', 40);
            $table->text('notas')->nullable();
            $table->string('estado', 20)->default('pendiente');
            $table->foreignId('procesado_por')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('procesado_en')->nullable();
            $table->timestamps();
            $table->index(['estado', 'created_at'], 'descartes_estado_fecha_idx');
            $table->index(['tipo_motivo', 'fecha_caducidad'], 'descartes_motivo_caducidad_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('descartes_productos');
    }
};
