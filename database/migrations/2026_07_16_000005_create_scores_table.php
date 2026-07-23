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
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_participant_id')->constrained('contest_participants')->onDelete('cascade');
            $table->decimal('duration_seconds', 6, 2); // X-axis (consecutive singing duration)
            $table->decimal('frequency_hz', 8, 2);      // Y-axis input (pitch)
            $table->integer('grade_score');             // Base score from config matching
            $table->integer('calculated_score');         // Final calculated score for this data point
            $table->boolean('is_active_tracking')->default(true); // Verification by CCTV motion tracking
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
