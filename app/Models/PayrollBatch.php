<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollBatch extends Model
{
    const STATUS_GENERATED = 'generated';
    const STATUS_LOCKED    = 'locked';

    protected $fillable = [
        'month',
        'year',
        'status',
        'total_employees',
        'total_payout',
        'generated_by',
        'locked_by',
        'locked_at',
    ];

    protected $casts = [
        'total_payout' => 'float',
        'locked_at'    => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED;
    }

    public function getMonthYearLabelAttribute(): string
    {
        return Carbon::create($this->year, $this->month)->format('F Y');
    }

    /** Returns true if this batch has the most recent month/year among all batches. */
    public function isLatest(): bool
    {
        $latest = static::orderByDesc('year')->orderByDesc('month')->first();
        return $latest && $latest->id === $this->id;
    }

    /** Find the previous calendar month's batch, if any. */
    public static function previousBatch(int $month, int $year): ?self
    {
        $prev = Carbon::create($year, $month)->subMonth();
        return static::where('month', $prev->month)->where('year', $prev->year)->first();
    }
}
