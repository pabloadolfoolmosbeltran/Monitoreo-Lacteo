<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {

            $table->id();

            $table->foreignId('produccion_id')
                  ->constrained('producciones')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('lectura_id')
                  ->constrained('lecturas')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->enum('tipo',[
                'Temperatura Alta',
                'Temperatura Baja'
            ]);

            $table->text('mensaje');

            $table->boolean('atendida')->default(false);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
