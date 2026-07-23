<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeConfig extends Model
{
    use HasFactory;

    protected $table = 'grades_config';

    protected $fillable = [
        'freq_min',
        'freq_max',
        'grade_name',
        'hex_color',
        'score',
    ];

    /**
     * Map a frequency (Hz) to a grade config record.
     */
    public static function findByFrequency(float $frequency): ?self
    {
        return self::where('freq_min', '<=', $frequency)
            ->where('freq_max', '>=', $frequency)
            ->first();
    }
}
