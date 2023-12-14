<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    use HasFactory;
    protected $fillable = [
        'job_title',
        'job_description',
        'wage',
        'business_id',
        'availability',
        'job_type_id',
        'duration',
        'start_date',
        'end_date',
        'qualifications',
        'urgency',
        'tasks',
        'payment_status'
    ];

    public function getTasksAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }
    public function getQualificationsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function profile()
    {
        return $this->business->profile();
    }
}
