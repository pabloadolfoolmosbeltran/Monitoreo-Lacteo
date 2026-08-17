<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('productos', function (Blueprint $table) {

        $table->decimal('temperatura_pasteurizacion',5,2)
              ->default(70)
              ->after('nombre');

    });
}

public function down(): void
{
    Schema::table('productos', function (Blueprint $table) {

        $table->dropColumn('temperatura_pasteurizacion');

    });
}
};
