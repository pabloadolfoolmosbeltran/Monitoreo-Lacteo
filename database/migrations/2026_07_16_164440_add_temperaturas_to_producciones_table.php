<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producciones', function (Blueprint $table) {

            $table->decimal('temperatura_inicial',5,2)
                  ->nullable()
                  ->after('temperatura_objetivo');

            $table->decimal('temperatura_final',5,2)
                  ->nullable()
                  ->after('temperatura_inicial');

        });
    }

    public function down(): void
    {
        Schema::table('producciones', function (Blueprint $table) {

            $table->dropColumn([
                'temperatura_inicial',
                'temperatura_final'
            ]);

        });
    }
};
