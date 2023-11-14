<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professional extends Model
{
    use HasFactory; 

    protected $fillable = [
        'user_id',
        'longitude',
        'latitude',
        'max_distance',
        'profession_title',
        'skills',
        'certifications',
        'years_of_experience',
        'wage',
        'status',
        'ratings',
    ];
}
