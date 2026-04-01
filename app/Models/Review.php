<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'review',
        'is_approved',
        'comment',
    ];

    /**
     * Возвращает товар, к которому относится отзыв.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Возвращает автора отзыва.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Возвращает голоса, оставленные по отзыву.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(ReviewVote::class);
    }
}
