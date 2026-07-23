<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bird extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ring_number',
        'owner_name',
        'bird_type',
        'photo_path',
    ];

    /**
     * Get the contest participations of the bird.
     */
    public function contestParticipations(): HasMany
    {
        return $this->hasMany(ContestParticipant::class);
    }
}
