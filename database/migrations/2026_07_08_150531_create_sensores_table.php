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
        Schema::create('sensores', function (Blueprint $table) {

            $table->id();

            // ESP32 al que pertenece
            $table->foreignId('dispositivo_id')
                  ->constrained('dispositivos')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            // Nombre del sensor
            $table->string('nombre');

            // Tipo de sensor
            $table->enum('tipo',[
                'DS18B20',
                'DHT22',
                'Otro'
            ])->default('DS18B20');

            // Unidad de medida
            $table->string('unidad',10)->default('°C');

            // Estado
            $table->enum('estado',[
                'Activo',
                'Inactivo',
                'Mantenimiento'
            ])->default('Activo');

            // Número de serie (opcional)
            $table->string('numero_serie')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensores');
    }
};
