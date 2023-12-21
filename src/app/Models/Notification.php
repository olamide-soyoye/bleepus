<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function professional(): HasOne
    {
        return $this->hasOne(Professional::class, 'id', 'professional_id');
    }

    // public function getBodyAttribute($value)
    // {
    //     return json_decode($value, true) ?? '';
    // }
}
