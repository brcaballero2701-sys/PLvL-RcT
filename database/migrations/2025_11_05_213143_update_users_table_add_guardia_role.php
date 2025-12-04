<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Asegura que la columna sea string normal
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 255)->default('user')->nullable(false)->change();
        });

        // 2) Borra constraint anterior si existía (Postgres)
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');

        // 3) Crea constraint nuevo con los valores permitidos
        DB::statement("
            ALTER TABLE users
            ADD CONSTRAINT users_role_check
            CHECK (role IN ('user', 'admin', 'guardia'))
        ");
    }

    public function down(): void
    {
        // Quita el constraint
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');

        // (Opcional) dejarlo solo como string default user
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 255)->default('user')->nullable(false)->change();
        });
    }
};

