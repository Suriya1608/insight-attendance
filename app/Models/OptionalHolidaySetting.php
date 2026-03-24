<?php

namespace App\Models;

use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;

class OptionalHolidaySetting extends Model
{
    use HasEncryptedRouteKey;

    protected $fillable = [
        'year',
        'max_allowed',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'year'   => 'integer',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Get the active setting for a given year, or null.
     */
    public static function forYear(int $year): ?self
    {
        return static::where('year', $year)->where('status', true)->first();
    }

    /**
     * Calculate pro-rata eligible optional holiday count for a user in a given year.
     *
     * Rules:
     *  - No DOJ on record          → full max_allowed
     *  - DOJ year matches $year    → floor((remaining_months / 12) * max_allowed), minimum 1
     *  - DOJ year is before $year  → full max_allowed
     *
     * @param  User      $user
     * @param  int       $year
     * @param  self|null $setting  Active setting for the year (pass result of forYear())
     * @return int
     */
    public static function getEligibleCount(User $user, int $year, ?self $setting): int
    {
        if (! $setting) {
            return 0;
        }

        $maxAllowed = (int) $setting->max_allowed;
        $doj        = $user->doj; // Cast to Carbon in User model

        // No DOJ recorded or DOJ before the queried year → full eligibility
        if (! $doj || $doj->year < $year) {
            return $maxAllowed;
        }

        // DOJ is in a future year (edge case: shouldn't normally happen)
        if ($doj->year > $year) {
            return 0;
        }

        // DOJ is within the same year → pro-rata
        $joiningMonth    = $doj->month;
        $remainingMonths = 12 - $joiningMonth + 1;
        $eligible        = (int) floor(($remainingMonths / 12) * $maxAllowed);

        return max(1, $eligible); // always grant at least 1
    }
}
