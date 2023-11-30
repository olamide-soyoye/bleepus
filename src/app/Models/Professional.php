<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Professional extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'longitude',
        'profile_id',
        'latitude',
        'max_distance',
        'profession_title',
        'skills',
        'certifications',
        'years_of_experience',
        'wage',
        'status',
        'ratings',
        'specialities',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'id', 'profile_id');
    }

    public function getSpecialitiesAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }
}
