<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContestParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'contest_id',
        'bird_id',
        'gantangan_number',
    ];

    /**
     * Get the contest.
     */
    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    /**
     * Get the bird.
     */
    public function bird(): BelongsTo
    {
        return $this->belongsTo(Bird::class);
    }

    /**
     * Get the score entries.
     */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    /**
     * Get cumulative calculated score.
     */
    public function getTotalScoreAttribute(): int
    {
        return (int) $this->scores()->sum('calculated_score');
    }
}
