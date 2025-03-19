<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'content',
        'author',
        'source',
        'length',
        'popularity_count',
        'user_id',
    ];

    /**
     * Get the user who created this quote
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get users who liked this quote
     */
    public function likedBy()
    {
        return $this->belongsToMany(User::class, 'quote_likes', 'quote_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Get users who favorited this quote
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'quote_favorites', 'quote_id', 'user_id')
            ->withTimestamps();
    }
}
