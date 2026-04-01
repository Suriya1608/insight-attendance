<?php

namespace App\Models;

use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\SiteSetting;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasEncryptedRouteKey;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'department_id',
        'employee_code',
        'mobile',
        'designation',
        'emp_status',
        'dob',
        'doj',
        'level1_manager_id',
        'level2_manager_id',
        'salary',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'dob'               => 'date',
            'doj'               => 'date',
            'salary'            => 'decimal:2',
        ];
    }

    /* ── Relationships ── */

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function employeeDetail()
    {
        return $this->hasOne(EmployeeDetail::class);
    }

    public function level1Manager()
    {
        return $this->belongsTo(User::class, 'level1_manager_id');
    }

    public function level2Manager()
    {
        return $this->belongsTo(User::class, 'level2_manager_id');
    }

    /* ── Helpers ── */

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isManager(): bool  { return $this->role === 'manager'; }
    public function isEmployee(): bool { return $this->role === 'employee'; }

    public function isActive(): bool   { return $this->emp_status === 'active'; }

    public function initials(): string
    {
        $words = explode(' ', trim($this->name));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 2));
    }

    public function profileImageUrl(): ?string
    {
        $path = $this->employeeDetail?->profile_image;
        return $path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
    }

    public static function generateEmployeeCode(): string
    {
        $prefix = strtoupper(SiteSetting::get('employee_id_prefix', 'EMP'));

        // Find the highest numeric suffix for this prefix
        $last = self::whereNotNull('employee_code')
                    ->where('employee_code', 'like', $prefix . '%')
                    ->orderByDesc('id')
                    ->value('employee_code');

        if ($last && preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $last, $m)) {
            $next = (int)$m[1] + 1;
        } else {
            $next = 1;
        }

        // Ensure uniqueness in case of gaps or concurrent inserts
        do {
            $code = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (self::where('employee_code', $code)->exists());

        return $code;
    }
}
