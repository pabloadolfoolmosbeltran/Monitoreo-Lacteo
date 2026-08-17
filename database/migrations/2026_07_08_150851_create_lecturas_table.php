<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecturas', function (Blueprint $table) {

            $table->id();

            $table->foreignId('produccion_id')
                  ->constrained('producciones')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('sensor_id')
                  ->constrained('sensores')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->decimal('temperatura',5,2);

            $table->timestamp('fecha_hora')->useCurrent();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecturas');
    }
};
