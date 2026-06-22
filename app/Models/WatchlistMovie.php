<?php

namespace App\Models;

use App\Enum\MovieStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class WatchlistMovie extends Pivot
{
    /** @use HasFactory<\Database\Factories\WatchlistMovieFactory> */
    use HasFactory;

    protected $casts = [
        'status' => MovieStatus::class,
    ];
}
