<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // ⛔ NO vuelvas a crear codigo_guardia si ya existe
            if (!Schema::hasColumn('users', 'codigo_guardia')) {
                $table->string('codigo_guardia')->nullable()->after('role');
            }

            if (!Schema::hasColumn('users', 'ubicacion_asignada')) {
                $table->string('ubicacion_asignada')->nullable()->after('codigo_guardia');
            }

            if (!Schema::hasColumn('users', 'turno_activo')) {
                $table->boolean('turno_activo')->default(false)->after('ubicacion_asignada');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // Solo elimina si existen
            if (Schema::hasColumn('users', 'turno_activo')) {
                $table->dropColumn('turno_activo');
            }

            if (Schema::hasColumn('users', 'ubicacion_asignada')) {
                $table->dropColumn('ubicacion_asignada');
            }

            // OJO: si codigo_guardia lo crea la otra migración,
            // NO lo borres aquí o se rompe el rollback.
            // Déjalo así:
            // if (Schema::hasColumn('users', 'codigo_guardia')) {
            //     $table->dropColumn('codigo_guardia');
            // }
        });
    }
};
