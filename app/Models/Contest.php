<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'status',
    ];

    /**
     * Get the participants for the contest.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(ContestParticipant::class);
    }
}
