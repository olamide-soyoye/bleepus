<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Profile extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'profile_pic',
        'phone_no',
        'country_abbr',
        'country_code',
        'agency_code',
        'address',
        'about', 
        'total_earnings',
        'pending_payment',
        'latitude',
        'longitude'
    ];

    public function business(): HasOne
    {
        return $this->hasOne(Business::class, 'profile_id', 'id');
    }

    public function professional(): HasOne
    {
        return $this->hasOne(Professional::class, 'profile_id', 'id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function jobListings()
    {
        return $this->hasManyThrough(
            JobListing::class,
            Business::class,
            'profile_id', // Foreign key on businesses table
            'business_id', // Foreign key on job_listings table
            'id', // Local key on profiles table
            'id' // Local key on businesses table
        );
    }
}
