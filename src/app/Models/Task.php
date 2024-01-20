<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'job_listing_id',
        'isCompleted'
    ];

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class);
    }
    protected $casts = [
        'isCompleted' => 'boolean',
    ];
    // public function jobListing()
    // {
    //     return $this->belongsTo(JobListing::class, 'job_listing_id');
    // }

    public function scopeCompleted($query)
    {
        return $query->where('isCompleted', true);
    }
    // public function getIsCompletedAttribute($value)
    // {
    //     return $value == 1 ? true : false;
    // }
    
}
