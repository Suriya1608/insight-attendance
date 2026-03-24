<?php

namespace App\Models;

use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CronJob extends Model
{
    use HasEncryptedRouteKey;
    protected $fillable = [
        'key',
        'name',
        'command',
        'description',
        'schedule_display',
        'is_active',
        'last_run_at',
        'last_run_status',
        'last_run_duration_ms',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'last_run_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function logs(): HasMany
    {
        return $this->hasMany(CronJobLog::class)->latest('started_at');
    }

    public function recentLogs(int $limit = 10): HasMany
    {
        return $this->hasMany(CronJobLog::class)->latest('started_at')->limit($limit);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function statusColor(): string
    {
        return match ($this->last_run_status) {
            'success' => 'green',
            'failed'  => 'red',
            default   => 'gray',
        };
    }

    public function formattedDuration(): string
    {
        if (! $this->last_run_duration_ms) {
            return '—';
        }
        $ms = $this->last_run_duration_ms;
        if ($ms < 1000) {
            return "{$ms}ms";
        }
        return round($ms / 1000, 2) . 's';
    }
}
