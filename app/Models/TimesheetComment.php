<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimesheetComment extends Model
{
    protected $fillable = [
        'timesheet_id',
        'user_id',
        'comment',
        'parent_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TimesheetComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TimesheetComment::class, 'parent_id')->orderBy('created_at');
    }
}
