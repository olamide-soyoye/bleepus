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
        'job_id',
        'job_type'
    ];

    public function professional(): HasOne
    {
        return $this->hasOne(Professional::class, 'id', 'professional_id');
    }

    protected $casts = [
        'read' => 'boolean',
    ];

    // public function getBodyAttribute($value)
    // {
    //     return json_decode($value, true) ?? '';
    // }
}
