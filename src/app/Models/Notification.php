<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = [
        'read',
        'body',
        'subject',
        'business_id',
        'professional_id',
        'job_id'
    ];

    // public function getBodyAttribute($value)
    // {
    //     return json_decode($value, true) ?? '';
    // }
}
