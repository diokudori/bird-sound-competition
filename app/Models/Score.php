<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'contest_participant_id',
        'duration_seconds',
        'frequency_hz',
        'grade_score',
        'calculated_score',
        'is_active_tracking',
    ];

    /**
     * Get the contest participant.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(ContestParticipant::class, 'contest_participant_id');
    }
}
