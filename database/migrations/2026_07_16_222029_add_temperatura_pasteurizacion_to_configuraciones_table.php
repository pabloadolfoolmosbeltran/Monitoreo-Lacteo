<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {

            $table->decimal('temperatura_pasteurizacion',5,2)
                  ->default(70.00)
                  ->after('temperatura_ventilador');

        });
    }

    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {

            $table->dropColumn('temperatura_pasteurizacion');

        });
    }
};
