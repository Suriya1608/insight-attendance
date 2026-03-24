<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferLetterEmail extends Model
{
    protected $fillable = [
        'offer_letter_id',
        'email',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function offerLetter(): BelongsTo
    {
        return $this->belongsTo(OfferLetter::class);
    }
}
