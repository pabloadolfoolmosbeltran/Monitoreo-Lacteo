<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('producciones', function (Blueprint $table) {
        $table->string('tipo_cuajo')->nullable()->after('cantidad_leche');
        $table->decimal('cantidad_cuajo', 8, 2)->nullable()->after('tipo_cuajo');
    });
}

public function down()
{
    Schema::table('producciones', function (Blueprint $table) {
        $table->dropColumn(['tipo_cuajo', 'cantidad_cuajo']);
    });
}
};
