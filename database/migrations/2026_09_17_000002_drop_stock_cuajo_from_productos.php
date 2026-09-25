<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('productos', 'stock_cuajo')) {
            Schema::table('productos', fn (Blueprint $table) => $table->dropColumn('stock_cuajo'));
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('productos', 'stock_cuajo')) {
            Schema::table('productos', fn (Blueprint $table) => $table
                ->decimal('stock_cuajo', 10, 2)
                ->nullable()
                ->after('unidad_cuajo'));
        }
    }
};
