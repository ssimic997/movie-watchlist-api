<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $title
 * @property Watchlist[] $watchlists
 */
class Movie extends Model
{
    use HasUlids;
    /**  @use HasFactory<\Database\Factories\MovieFactory> */
    use HasFactory;

    protected $fillable = ['title'];

    public function watchlists(): BelongsToMany
    {
        return $this->belongsToMany(Watchlist::class, 'watchlist_movies')
            ->using(WatchlistMovie::class)
            ->withPivot(['status', 'user_rating'])
            ->withTimestamps();
    }

    public function externalIds(): HasMany
    {
        return $this->hasMany(MovieExternalId::class);
    }

    public function metadata(): HasMany
    {
        return $this->hasMany(MovieMetadata::class);
    }



}
