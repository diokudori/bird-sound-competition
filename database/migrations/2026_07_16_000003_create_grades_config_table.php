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
        Schema::create('grades_config', function (Blueprint $table) {
            $table->id();
            $table->decimal('freq_min', 8, 2); // Minimum frequency in Hz
            $table->decimal('freq_max', 8, 2); // Maximum frequency in Hz
            $table->string('grade_name');       // e.g. Grade A, Grade B, dst.
            $table->string('hex_color', 7);     // CSS Hex color e.g. #FF5733
            $table->integer('score');           // Score weighting for this grade
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades_config');
    }
};
