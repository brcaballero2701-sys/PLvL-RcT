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
        // RF033: Tabla para control de dispositivos conectados (lectores de código de barras)
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('reader_code')->unique()->comment('Código único del lector');
            $table->string('ip_address')->nullable()->comment('IP del dispositivo');
            $table->string('mac_address')->nullable()->comment('MAC address del dispositivo');
            $table->string('ubicacion')->nullable()->comment('Ubicación física del lector');
            $table->boolean('active')->default(true)->comment('Estado del dispositivo');
            $table->timestamp('last_ping')->nullable()->comment('Último ping/actividad');
            $table->foreignId('guardia_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('modelo')->nullable()->comment('Modelo del lector');
            $table->string('firmware')->nullable()->comment('Versión de firmware');
            $table->integer('intentos_fallidos')->default(0)->comment('Intentos fallidos de conexión');
            $table->text('notas')->nullable();
            $table->timestamps();

            // Índices para búsqueda rápida
            $table->index('reader_code');
            $table->index('active');
            $table->index('last_ping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
