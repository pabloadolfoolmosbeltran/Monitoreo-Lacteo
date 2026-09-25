<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('Administrador','Trabajador','Productor') NOT NULL DEFAULT 'Trabajador'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('users')
            ->where('rol', 'Productor')
            ->update(['rol' => 'Trabajador']);

        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('Administrador','Trabajador') NOT NULL DEFAULT 'Trabajador'");
    }
};
