<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
        'password' => 'hashed',
    ];

    /**
     * Check if user is an admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a moderator
     *
     * @return bool
     */
    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    /**
     * Get quotes created by this user
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * Get quotes liked by this user
     */
    public function likedQuotes()
    {
        return $this->belongsToMany(Quote::class, 'quote_likes', 'user_id', 'quote_id')
            ->withTimestamps();
    }

    /**
     * Get quotes favorited by this user
     */
    public function favoriteQuotes()
    {
        return $this->belongsToMany(Quote::class, 'quote_favorites', 'user_id', 'quote_id')
            ->withTimestamps();
    }
}
