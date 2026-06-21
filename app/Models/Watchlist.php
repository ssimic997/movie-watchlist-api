<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $id
 * @property string $name
 * @property User $user
 */
class Watchlist extends Model
{
    /** @use HasFactory<\Database\Factories\WatchlistFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = ['name', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'watchlist_movies')
            ->using(WatchlistMovie::class)
            ->withPivot(['status', 'user_rating'])
            ->withTimestamps()
            ;
    }

}
