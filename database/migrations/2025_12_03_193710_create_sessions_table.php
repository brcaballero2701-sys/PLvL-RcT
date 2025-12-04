<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Si ya existe, no hacer nada (evita error en Render)
        if (Schema::hasTable('sesiones')) {
            return;
        }

        Schema::create('sesiones', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('id_usuario')->nullable()->index();
            $table->string('dirección_ip', 45)->nullable();
            $table->text('agente_usuario')->nullable();
            $table->longText('carga útil');
            $table->integer('última_actividad')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones');
    }
};
