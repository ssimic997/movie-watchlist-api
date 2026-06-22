<?php

namespace App\Services;

use App\Contracts\MovieApiProviderContract;
use App\Contracts\MovieRepositoryContract;
use App\Jobs\FetchMovieMetadataJob;
use App\Models\User;

class MovieService
{
    public function __construct(
        private readonly MovieRepositoryContract $movieRepository,
        private readonly MovieApiProviderContract $movieProvider,
    ){}

    public function refetchMetadata(string $movieId): void
    {
        $movie = $this->movieRepository->findById($movieId);
        $providerName = $this->movieProvider->providerName();

        $externalIdRecord = $movie->externalIds()
            ->where('provider', $providerName)
            ->first();

        FetchMovieMetadataJob::dispatch(
            $movie,
            $providerName,
            $externalIdRecord?->external_id,
            $externalIdRecord ? null : $movie->title,
        );
    }
}
