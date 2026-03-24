<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfferLetter extends Model
{
    protected $fillable = [
        'name',
        'address',
        'email',
        'mobile',
        'designation',
        'joining_date',
        'ctc',
        'offer_date',
        'content',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'offer_date'   => 'date',
        'ctc'          => 'decimal:2',
    ];

    public function emailLogs(): HasMany
    {
        return $this->hasMany(OfferLetterEmail::class);
    }
}
