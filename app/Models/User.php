<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'username',
        'password',
        'bio',
        'is_private'
    ];

    
    public $timestamps = false;

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

    public function following()
    {
        return $this->belongsToMany(User::class, 'follow', 'follower_id' , 'following_id')->withPivot('is_accepted');
    }
    public function follower()
    {
        return $this->belongsToMany(User::class, 'follow', 'following_id' , 'follower_id')->withPivot('is_accepted');
    }

    public function posts()
    {
        return $this->hasMany(Posts::class);
    }
    
}
