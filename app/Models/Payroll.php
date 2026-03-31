<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'total_days',
        'effective_days',
        'working_days',
        'present_days',
        'lop_days',
        'permission_hours',
        'optional_holidays_taken',
        'salary',
        'per_day_salary',
        'lop_amount',
        'net_salary',
        'generated_by',
    ];

    protected $casts = [
        'present_days'    => 'float',
        'lop_days'        => 'float',
        'permission_hours'=> 'float',
        'salary'          => 'float',
        'per_day_salary'  => 'float',
        'lop_amount'      => 'float',
        'net_salary'      => 'float',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getMonthNameAttribute(): string
    {
        return Carbon::create($this->year, $this->month)->format('F');
    }

    public function getMonthYearLabelAttribute(): string
    {
        return Carbon::create($this->year, $this->month)->format('F Y');
    }
}
