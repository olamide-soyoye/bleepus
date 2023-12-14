<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplicant extends Model
{
    use HasFactory;
    protected $fillable = [
        'professional_id',
        'job_listing_id',
        'status' 
    ];

    public function getTasksAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function getQualificationsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }
    public function getSpecialitiesAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }
    
    public function jobListing()
    {
        return $this->belongsTo(JobListing::class);
    }

    
    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }
}