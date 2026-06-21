<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovieExternalId extends Model
{
    /** @use HasFactory<\Database\Factories\MovieExternalIdFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = ['movie_id', 'provider', 'external_id'];

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }
}
