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
