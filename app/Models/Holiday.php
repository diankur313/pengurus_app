<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['title', 'date', 'description'];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Check if a given date is a holiday (or weekend).
     */
    public static function isNonWorkingDay(\Carbon\Carbon $date): bool
    {
        if ($date->isWeekend()) {
            return true;
        }
        return static::where('date', $date->toDateString())->exists();
    }

    /**
     * Add N working days to a date, skipping weekends and holidays.
     */
    public static function addWorkingDays(\Carbon\Carbon $date, int $days): \Carbon\Carbon
    {
        $result = $date->copy();
        $added  = 0;
        while ($added < $days) {
            $result->addDay();
            if (!static::isNonWorkingDay($result)) {
                $added++;
            }
        }
        return $result;
    }
}
