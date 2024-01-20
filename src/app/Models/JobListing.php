<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    use HasFactory;
    protected $appends = ['tasksData'];
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

    public function tasks()
    {
        return $this->hasMany(Task::class, 'job_listing_id');
    }

    public function getTasksDataAttribute()
    {
        return $this->tasks()->get();
    }


    public function tasksWithPercentageComplete(){
        $tasks = $this->tasks; 

        $totalTasks = count($tasks); 

        return $tasks;
        if ($totalTasks === 0) {
            return [
                'tasks' => $tasks,
                'percentageComplete' => 0,
            ];
        }

        $completedTasks = $tasks->filter(function ($task) {
            return $task->isCompleted;
        })->count();

        $percentageComplete = ($completedTasks / $totalTasks) * 100;

        return [
            'tasks' => $tasks,
            'percentageComplete' => $percentageComplete,
        ];
    }

    public function getTasksWithPercentageCompleteAttribute()
    {
        return $this->tasksWithPercentageComplete();
    }   
}
