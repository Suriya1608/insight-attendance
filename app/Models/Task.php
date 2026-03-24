<?php

namespace App\Models;

use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasEncryptedRouteKey;

    protected $fillable = [
        'title', 'description', 'assigned_to', 'assigned_by',
        'priority', 'start_date', 'due_date', 'status', 'attachment',
        'started_at', 'completed_at',
    ];

    protected $casts = [
        'start_date'   => 'date',
        'due_date'     => 'date',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isOverdue(): bool
    {
        return $this->status !== 'completed' && $this->due_date->isPast();
    }

    public function statusLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function priorityLabel(): string
    {
        return ucfirst($this->priority);
    }
}
