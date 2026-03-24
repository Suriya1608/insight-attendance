<?php

namespace App\Models;

use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceRegularization extends Model
{
    use HasEncryptedRouteKey;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'date',
        'request_type',
        'original_punch_in',
        'original_punch_out',
        'requested_punch_in',
        'requested_punch_out',
        'reason',
        'attachment_path',
        'status',
        'l1_manager_id',
        'l2_manager_id',
        'l1_comment',
        'l2_comment',
        'submitted_at',
        'l1_actioned_at',
        'l2_actioned_at',
        'finalized_at',
    ];

    protected $casts = [
        'date' => 'date',
        'submitted_at' => 'datetime',
        'l1_actioned_at' => 'datetime',
        'l2_actioned_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function l1Manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'l1_manager_id');
    }

    public function l2Manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'l2_manager_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(AttendanceRegularizationComment::class)->orderBy('created_at');
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

    public function getTypeLabelAttribute(): string
    {
        return config('attendance_regularization.types.' . $this->request_type, ucwords(str_replace('_', ' ', $this->request_type)));
    }

    public function getRequestedTimesLabelAttribute(): string
    {
        $in = $this->requested_punch_in ? substr($this->requested_punch_in, 0, 5) : '-';
        $out = $this->requested_punch_out ? substr($this->requested_punch_out, 0, 5) : '-';

        return $in . ' -> ' . $out;
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
