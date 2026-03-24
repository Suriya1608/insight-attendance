<?php

namespace App\Models;

use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimesheetEntry extends Model
{
    use HasEncryptedRouteKey;

    protected $fillable = [
        'timesheet_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'duration_minutes',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
    ];

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }

    public function getFormattedDurationAttribute(): string
    {
        $h = intdiv($this->duration_minutes, 60);
        $m = $this->duration_minutes % 60;

        if ($h > 0 && $m > 0) {
            return $h . 'h ' . str_pad((string) $m, 2, '0', STR_PAD_LEFT) . 'm';
        }

        if ($h > 0) {
            return $h . 'h';
        }

        return $m . 'm';
    }
}
