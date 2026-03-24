<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PunchLog extends Model
{
    protected $fillable = [
        'user_id', 'attendance_id', 'type', 'punched_at',
        'ip_address', 'latitude', 'longitude', 'location_label', 'formatted_address',
        'suburb', 'city', 'state', 'country',
    ];

    protected $casts = [
        'punched_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
