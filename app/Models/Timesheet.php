<?php

namespace App\Models;

use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timesheet extends Model
{
    use HasEncryptedRouteKey;

    protected $fillable = [
        'user_id',
        'date',
        'status',
        'l1_manager_id',
        'l2_manager_id',
        'l1_remarks',
        'l2_remarks',
        'l1_actioned_at',
        'l2_actioned_at',
        'submitted_at',
    ];

    protected $casts = [
        'date' => 'date',
        'l1_actioned_at' => 'datetime',
        'l2_actioned_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function l1Manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'l1_manager_id');
    }

    public function l2Manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'l2_manager_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TimesheetEntry::class)->orderBy('start_time');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TimesheetComment::class)->orderBy('created_at');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPendingL1(): bool
    {
        return $this->status === 'pending_l1';
    }

    public function isPendingL2(): bool
    {
        return $this->status === 'pending_l2';
    }

    public function isSubmitted(): bool
    {
        return $this->isPendingL1();
    }

    public function isApprovedL1(): bool
    {
        return $this->isPendingL2();
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'rejected'], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'pending_l1' => 'Pending L1 Review',
            'pending_l2' => 'Pending L2 Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'pending_l1' => 'orange',
            'pending_l2' => 'blue',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }

    public function getTotalMinutesAttribute(): int
    {
        return $this->entries->sum('duration_minutes');
    }

    public function getFormattedTotalHoursAttribute(): string
    {
        $total = $this->total_minutes;
        $hours = intdiv($total, 60);
        $minutes = $total % 60;

        return $hours . 'h ' . str_pad((string) $minutes, 2, '0', STR_PAD_LEFT) . 'm';
    }

    public function scopePendingApprovalBy($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('l1_manager_id', $userId)->where('status', 'pending_l1')
                ->orWhere(function ($q2) use ($userId) {
                    $q2->where('l2_manager_id', $userId)->where('status', 'pending_l2');
                });
        });
    }
}
