<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar la migración.
     */
    public function up(): void
    {
        Schema::create('producciones', function (Blueprint $table) {
            $table->id();

            // Relación con usuarios
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            // Relación directa con tu tabla 'productos'
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();

            // Relación con dispositivos (ESP32) si aplica
            $table->unsignedBigInteger('dispositivo_id')->nullable();

            // Campos enviados desde tu formulario web (`ProduccionController`)
            $table->decimal('cantidad_leche', 8, 2);
            $table->decimal('temperatura_objetivo', 5, 2);
            $table->string('estado')->default('En proceso');
            $table->string('etapa')->default('Produccion');
            $table->text('observaciones')->nullable();

            // Fechas del proceso
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('producciones');
    }
};