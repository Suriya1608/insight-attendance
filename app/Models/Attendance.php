<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    protected $fillable = [
        'user_id', 'date', 'status', 'punch_in', 'punch_out', 'work_hours', 'permission_hours', 'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function punchLogs(): HasMany
    {
        return $this->hasMany(PunchLog::class)->orderBy('punched_at');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function getFormattedWorkHoursAttribute(): string
    {
        if (! $this->work_hours) {
            return '—';
        }
        $h = (int) floor($this->work_hours);
        $m = (int) round(($this->work_hours - $h) * 60);
        return "{$h}h {$m}m";
    }
}
