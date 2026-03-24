<?php

namespace App\Models;

use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CronJobLog extends Model
{
    use HasEncryptedRouteKey;
    protected $fillable = [
        'cron_job_id',
        'triggered_by',
        'trigger_type',
        'status',
        'output',
        'duration_ms',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function cronJob(): BelongsTo
    {
        return $this->belongsTo(CronJob::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function formattedDuration(): string
    {
        if (! $this->duration_ms) {
            return '—';
        }
        $ms = $this->duration_ms;
        if ($ms < 1000) {
            return "{$ms}ms";
        }
        return round($ms / 1000, 2) . 's';
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'success' => 'green',
            'failed'  => 'red',
            'running' => 'blue',
            default   => 'gray',
        };
    }
}
