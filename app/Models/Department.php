<?php

namespace App\Models;

use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasEncryptedRouteKey;
    protected $fillable = [
        'name', 'code', 'description', 'saturday_rule', 'has_saturday_leave', 'status',
        'cl_per_month', 'permissions_per_month', 'hours_per_permission',
        'leave_rule_active', 'leave_rule_notes',
    ];

    protected $casts = [
        'has_saturday_leave'   => 'boolean',
        'leave_rule_active'    => 'boolean',
        'cl_per_month'         => 'float',
        'permissions_per_month'=> 'integer',
        'hours_per_permission' => 'integer',
    ];

    public function employees()
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** Whether this department's saturday rule grants a flexible/carry-forward day off. */
    public function hasSaturdayLeave(): bool
    {
        return in_array($this->saturday_rule, ['flexible_saturday', 'carry_forward']);
    }

    public function saturdayRuleLabel(): string
    {
        return match($this->saturday_rule) {
            '2nd_saturday_off'  => '2nd Saturday Off (Fixed)',
            '4th_saturday_off'  => '4th Saturday Off (Fixed)',
            'flexible_saturday' => '1 Flexible Saturday / Month',
            'carry_forward'     => 'Carry Forward if All Saturdays Worked',
            // legacy values
            'all_saturdays_off'     => 'All Saturdays Off',
            'alternating_saturdays' => 'Alternating Saturdays',
            default                 => 'All Saturdays Working',
        };
    }
}
