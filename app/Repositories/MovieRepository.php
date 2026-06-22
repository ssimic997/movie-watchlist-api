<?php

namespace App\Repositories;

use App\Contracts\MovieRepositoryContract;
use App\Enum\MovieMetadataStatus;
use App\Models\Movie;
use App\Models\MovieExternalId;

class MovieRepository implements MovieRepositoryContract
{
    public function findExternalId(string $provider, string $externalId): ?MovieExternalId
    {
        return MovieExternalId::with('movie')
            ->where('provider', $provider)
            ->where('external_id', $externalId)
            ->first();
    }

    public function createWithExternalId(string $provider, string $externalId): Movie
    {
        $movie = Movie::query()->create(['title' => $externalId]);
        $movie->externalIds()->create([
            'provider'    => $provider,
            'external_id' => $externalId,
        ]);

        return $movie;
    }

    public function createWithTitle(string $title): Movie
    {
        return Movie::query()->create(['title' => $title]);
    }

    public function hasMetadataForProvider(Movie $movie, string $provider): bool
    {
        return $movie->metadata()
            ->where('provider', $provider)
            ->where('metadata_status', MovieMetadataStatus::SUCCESSFUL)
            ->exists();
    }

    public function findById(string $movieId): Movie
    {
        return Movie::query()->findOrFail($movieId);
    }
}
