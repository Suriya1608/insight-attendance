<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'leave_type',
        'transaction_type',
        'amount',
        'year',
        'month',
        'date',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'float',
        'date'   => 'date',
        'year'   => 'integer',
        'month'  => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check whether a credit transaction already exists for a user / type / year / month
     * to prevent double-crediting if the command is accidentally run twice.
     */
    public static function alreadyCredited(int $userId, string $type, int $year, int $month): bool
    {
        return self::where('user_id', $userId)
            ->where('leave_type', $type)
            ->where('transaction_type', 'credit')
            ->where('year', $year)
            ->where('month', $month)
            ->exists();
    }
}
