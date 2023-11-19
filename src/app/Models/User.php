<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Netsells\GeoScope\Traits\GeoScopeTrait;
use Illuminate\Support\Str;
use Illuminate\Auth\Passwords\CanResetPassword;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;
    use GeoScopeTrait;

    protected $keyType = 'uuid';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::uuid();
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fname',
        'lname',
        'mname',
        'email',
        'password',
        'phone_no',
        'user_type_id',
        'image',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function professional(): HasOne
    {
        return $this->hasOne(Professional::class);
    }

    public function business(): HasOne
    {
        return $this->hasOne(Business::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function deleteBusinessAccount()
    {
        $this->profile()->delete();
        $this->business()->delete();

        // Delete the user
        $this->delete();
    }

    public function deleteProfessionalAccount()
    {
        $this->profile()->delete();
        $this->professional()->delete();

        // Delete the user
        $this->delete();
    }
}
