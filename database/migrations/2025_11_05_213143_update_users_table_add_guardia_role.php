<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Asegura que la columna sea string normal en cualquier motor
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 255)
                ->default('user')
                ->nullable(false)
                ->change();
        });

        // 2) SOLO Postgres: borrar constraint anterior si existía
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');

            // 3) SOLO Postgres: crear constraint nuevo
            DB::statement("
                ALTER TABLE users
                ADD CONSTRAINT users_role_check
                CHECK (role IN ('user', 'admin', 'guardia'))
            ");
        }
    }

    public function down(): void
    {
        // Revertir el constraint SOLO en Postgres
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        }

        // Dejar role como string default user
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 255)
                ->default('user')
                ->nullable(false)
                ->change();
        });
    }
};
