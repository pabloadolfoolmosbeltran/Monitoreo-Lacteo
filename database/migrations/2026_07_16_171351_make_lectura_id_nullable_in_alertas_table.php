<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alertas', function (Blueprint $table) {
            // Hacemos que lectura_id pueda ser NULL en la base de datos
            $table->foreignId('lectura_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('alertas', function (Blueprint $table) {
            // Revertimos el cambio si se hace un rollback
            $table->foreignId('lectura_id')->nullable(false)->change();
        });
    }
};
