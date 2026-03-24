<?php

namespace App\Models;

use App\Traits\HasEncryptedRouteKey;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    use HasEncryptedRouteKey;
    protected $fillable = [
        'name',
        'date',
        'type',
        'description',
        'status',
        'scope',
        'department_id',
    ];

    protected $casts = [
        'date'   => 'date',
        'status' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getDayNameAttribute(): string
    {
        return $this->date->format('l'); // Monday, Tuesday, …
    }

    public function getScopeLabel(): string
    {
        if ($this->scope === 'department' && $this->department) {
            return $this->department->name;
        }
        return 'All Departments';
    }

    public function isNational(): bool
    {
        return $this->type === 'national';
    }
}
