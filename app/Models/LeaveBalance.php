<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = [
        'user_id',
        'leave_type',
        'year',
        'month',
        'credited',
        'used',
        'lapsed',
    ];

    protected $casts = [
        'credited' => 'float',
        'used'     => 'float',
        'lapsed'   => 'float',
        'year'     => 'integer',
        'month'    => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Remaining usable balance (cannot go below zero).
     */
    public function getBalanceAttribute(): float
    {
        return max(0.0, $this->credited - $this->used - $this->lapsed);
    }

    /**
     * Fetch (or initialise) the annual CL row for a user.
     */
    public static function clForYear(int $userId, int $year): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId, 'leave_type' => 'CL', 'year' => $year, 'month' => null],
            ['credited' => 0, 'used' => 0, 'lapsed' => 0]
        );
    }

    /**
     * Fetch (or initialise) a monthly row for permission / saturday_leave.
     */
    public static function monthlyFor(int $userId, string $type, int $year, int $month): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId, 'leave_type' => $type, 'year' => $year, 'month' => $month],
            ['credited' => 0, 'used' => 0, 'lapsed' => 0]
        );
    }
}
