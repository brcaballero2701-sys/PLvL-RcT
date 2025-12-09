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
        Schema::table('users', function (Blueprint $table) {
            $table->string('theme_preference')->default('light')->after('role'); // light, dark, system
            $table->boolean('high_contrast')->default(false)->after('theme_preference');
            $table->boolean('reduce_motion')->default(false)->after('high_contrast');
            $table->enum('font_size', ['small', 'normal', 'large', 'extra-large'])->default('normal')->after('reduce_motion');
            $table->enum('line_spacing', ['tight', 'normal', 'loose', 'extra-loose'])->default('normal')->after('font_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['theme_preference', 'high_contrast', 'reduce_motion', 'font_size', 'line_spacing']);
        });
    }
};
