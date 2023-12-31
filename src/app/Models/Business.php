<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Business extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'longitude',
        'latitude',
        'max_distance',
        'ratings',
        'company_name',
        'profile_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'id', 'profile_id');
    }

    // public function profile()
    // {
    //     return $this->belongsTo(Profile::class, 'profile_id');
    // }
}
