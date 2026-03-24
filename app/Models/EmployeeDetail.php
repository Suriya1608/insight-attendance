<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDetail extends Model
{
    protected $fillable = [
        'user_id',
        'aadhaar_number',
        'pan_number',
        'bank_name',
        'bank_account_number',
        'ifsc_code',
        'emergency_contact',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'country',
        'blood_group',
        'profile_image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
