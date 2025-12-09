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
        // RF024: Agregar índices para optimizar performance <2s
        Schema::table('asistencias', function (Blueprint $table) {
            // Índice compuesto para búsquedas frecuentes
            if (!$this->indexExists('asistencias', 'idx_asistencias_instructor_fecha')) {
                $table->index(['instructor_id', 'fecha_hora_registro'], 'idx_asistencias_instructor_fecha');
            }
            if (!$this->indexExists('asistencias', 'idx_asistencias_guardia_fecha')) {
                $table->index(['guardia_id', 'fecha_hora_registro'], 'idx_asistencias_guardia_fecha');
            }
            if (!$this->indexExists('asistencias', 'idx_asistencias_tipo_fecha')) {
                $table->index(['tipo_movimiento', 'fecha_hora_registro'], 'idx_asistencias_tipo_fecha');
            }
            if (!$this->indexExists('asistencias', 'idx_asistencias_tardanza_fecha')) {
                $table->index(['es_tardanza', 'fecha_hora_registro'], 'idx_asistencias_tardanza_fecha');
            }
        });

        Schema::table('instructors', function (Blueprint $table) {
            // Índice para búsquedas por documento_identidad (si no existe)
            if (!$this->indexExists('instructors', 'instructors_documento_identidad_unique')) {
                $table->unique('documento_identidad');
            }
            if (!$this->indexExists('instructors', 'idx_instructors_activo')) {
                $table->index('activo', 'idx_instructors_activo');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            // Índice para login rápido
            if (!$this->indexExists('users', 'idx_users_email')) {
                $table->index('email', 'idx_users_email');
            }
            if (!$this->indexExists('users', 'idx_users_role')) {
                $table->index('role', 'idx_users_role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropIndex('idx_asistencias_instructor_fecha');
            $table->dropIndex('idx_asistencias_guardia_fecha');
            $table->dropIndex('idx_asistencias_tipo_fecha');
            $table->dropIndex('idx_asistencias_tardanza_fecha');
        });

        Schema::table('instructors', function (Blueprint $table) {
            $table->dropUnique(['documento_identidad']);
            $table->dropIndex('idx_instructors_activo');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_role');
        });
    }

    /**
     * Verificar si un índice ya existe
     */
    private function indexExists(string $table, string $index): bool
    {
        return collect(\DB::select("PRAGMA index_list({$table})"))->pluck('name')->contains($index);
    }
};
