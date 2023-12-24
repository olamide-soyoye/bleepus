<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'otp',
        'used',
        'expiry'
    ];

    protected $casts = [
        'used' => 'boolean',
    ];
}
