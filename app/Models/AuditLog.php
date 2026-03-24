<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    // Audit logs are immutable — never editable or deletable
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'module_name',
        'record_id',
        'action_type',
        'old_value',
        'new_value',
        'performed_by',
        'performed_at',
        'ip_address',
    ];

    protected $casts = [
        'old_value'    => 'array',
        'new_value'    => 'array',
        'performed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // ── Helper: write a log entry ─────────────────────────────────────────────

    public static function record(
        string $module,
        string $action,
        ?int   $recordId   = null,
        ?int   $userId     = null,
        mixed  $oldValue   = null,
        mixed  $newValue   = null,
    ): void {
        static::create([
            'user_id'      => $userId,
            'module_name'  => $module,
            'record_id'    => $recordId,
            'action_type'  => $action,
            'old_value'    => $oldValue,
            'new_value'    => $newValue,
            'performed_by' => auth()->id(),
            'performed_at' => now(),
            'ip_address'   => request()->ip(),
        ]);
    }
}
