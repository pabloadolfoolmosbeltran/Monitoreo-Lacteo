<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table) {

            $table->id();

            $table->integer('intervalo_lectura')->default(5);

            $table->decimal('temperatura_ventilador',5,2)->default(45.00);

            $table->boolean('motor_automatico')->default(true);

            $table->boolean('ventilador_automatico')->default(true);

            $table->string('nombre_sistema')->default('Sistema Inteligente de Producción Láctea');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
