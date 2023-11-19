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
        'address',
        'about', 
        'total_earnings'
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
}
