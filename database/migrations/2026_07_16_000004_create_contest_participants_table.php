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
        Schema::create('contest_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained()->onDelete('cascade');
            $table->foreignId('bird_id')->constrained()->onDelete('cascade');
            $table->integer('gantangan_number'); // Cages are placed at numbered slots (gantangan)
            $table->timestamps();

            // Unique constraints
            $table->unique(['contest_id', 'bird_id']);
            $table->unique(['contest_id', 'gantangan_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contest_participants');
    }
};
