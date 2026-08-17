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
    Schema::table('sensores', function (Blueprint $table) {

        $table->decimal('temperatura_actual',5,2)
              ->nullable()
              ->after('estado');

    });
}

public function down(): void
{
    Schema::table('sensores', function (Blueprint $table) {

        $table->dropColumn('temperatura_actual');

    });
}
};
