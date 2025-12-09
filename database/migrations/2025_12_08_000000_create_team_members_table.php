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
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255)->unique();
            $table->string('rol', 100); // 'Instructora líder', 'Desarrollador 1', etc.
            $table->string('email', 255)->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('cedula', 20)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('foto_url', 255)->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('rol');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
