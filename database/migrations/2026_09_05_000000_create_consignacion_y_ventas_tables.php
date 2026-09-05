<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('fecha_entrada');
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['abierta', 'liquidada', 'devuelta'])->default('abierta');
            $table->timestamps();

            $table->index(['user_id', 'estado']);
        });

        Schema::create('consignacion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consignacion_id')->constrained('consignaciones')->restrictOnDelete();
            $table->foreignId('presentacion_id')->constrained('presentaciones')->restrictOnDelete();
            $table->decimal('cantidad_recibida', 10, 3);
            $table->decimal('cantidad_disponible', 10, 3);
            $table->decimal('precio_productor', 10, 2);
            $table->decimal('precio_venta', 10, 2);
            $table->timestamps();

            $table->index(['consignacion_id', 'presentacion_id']);
        });

        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consignacion_item_id')->constrained('consignacion_items')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->decimal('cantidad_vendida', 10, 3);
