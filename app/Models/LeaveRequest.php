<?php

namespace App\Models;

use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasEncryptedRouteKey;
    protected $fillable = [
        'user_id',
        'request_type',
        'leave_type',
        'request_date',
        'from_date',
        'to_date',
        'total_days',
        'cl_days',
        'lop_days',
        'permission_hours',
        'reason',
        'attachment',
        'status',
        'l1_manager_id',
        'l2_manager_id',
        'l1_remarks',
        'l2_remarks',
        'l1_actioned_at',
        'l2_actioned_at',
        'auto_lop',
    ];

    protected $casts = [
        'request_date'    => 'date',
        'from_date'       => 'date',
        'to_date'         => 'date',
        'total_days'      => 'integer',
        'cl_days'         => 'integer',
        'lop_days'        => 'integer',
        'l1_actioned_at'  => 'datetime',
        'l2_actioned_at'  => 'datetime',
        'permission_hours'=> 'float',
        'auto_lop'        => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

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

    // ── Status helpers ────────────────────────────────────────────────────────

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isApprovedL1(): bool { return $this->status === 'approved_l1'; }
    public function isApproved(): bool   { return $this->status === 'approved'; }
    public function isRejected(): bool   { return $this->status === 'rejected'; }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'     => 'Pending',
            'approved_l1' => 'Approved (L1)',
            'approved'    => 'Approved',
            'rejected'    => 'Rejected',
            default       => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'     => 'orange',
            'approved_l1' => 'blue',
            'approved'    => 'green',
            'rejected'    => 'red',
            default       => 'gray',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        if ($this->request_type === 'permission') {
            return 'Permission';
        }
        return match($this->leave_type) {
            'CL'            => 'Casual Leave',
            'LOP'           => 'Loss of Pay',
            'saturday_leave'=> 'Saturday Leave',
            default         => ucfirst($this->leave_type ?? ''),
        };
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /**
     * Requests pending THIS user's approval as L1 or L2 manager.
     */
    public function scopePendingApprovalBy($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('l1_manager_id', $userId)->where('status', 'pending')
              ->orWhere(function ($q2) use ($userId) {
                  $q2->where('l2_manager_id', $userId)->where('status', 'approved_l1');
              });
        });
    }
}
