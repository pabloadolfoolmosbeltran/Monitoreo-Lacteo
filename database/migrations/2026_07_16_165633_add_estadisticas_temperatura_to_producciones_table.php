<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producciones', function (Blueprint $table) {

            $table->decimal('temperatura_minima',5,2)
                  ->nullable()
                  ->after('temperatura_final');

            $table->decimal('temperatura_maxima',5,2)
                  ->nullable()
                  ->after('temperatura_minima');

            $table->decimal('temperatura_promedio',5,2)
                  ->nullable()
                  ->after('temperatura_maxima');

        });
    }

    public function down(): void
    {
        Schema::table('producciones', function (Blueprint $table) {

            $table->dropColumn([
                'temperatura_minima',
                'temperatura_maxima',
                'temperatura_promedio'
            ]);

        });
    }
};
