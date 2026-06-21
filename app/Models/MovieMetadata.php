<?php

namespace App\Models;

use App\Enum\MovieMetadataStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovieMetadata extends Model
{
    /** @use HasFactory<\Database\Factories\MovieMetadataFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'movie_id',
        'provider',
        'metadata_status',
        'year',
        'rated',
        'runtime',
        'genre',
        'director',
        'writer',
        'actors',
        'plot',
        'poster_url',
        'provider_rating',
        'raw_response',
    ];

    protected $casts = [
        'metadata_status' => MovieMetadataStatus::class,
        'raw_response'    => 'array',
    ];

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }
}
