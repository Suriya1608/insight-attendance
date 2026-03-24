<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceRegularizationComment extends Model
{
    protected $fillable = [
        'attendance_regularization_id',
        'user_id',
        'comment',
        'parent_id',
    ];

    public function regularization(): BelongsTo
    {
        return $this->belongsTo(AttendanceRegularization::class, 'attendance_regularization_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at');
    }
}
