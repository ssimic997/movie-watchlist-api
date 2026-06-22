<?php

namespace App\Services;

use App\Contracts\MovieApiProviderContract;
use App\Contracts\MovieRepositoryContract;
use App\Contracts\WatchlistRepositoryContract;
use App\Enum\MovieStatus;
use App\Events\MovieAddedToWatchlist;
use App\Exceptions\MovieAlreadyInWatchlistException;
use App\Jobs\FetchMovieMetadataJob;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WatchlistMovieService
{
    public function __construct(
        private readonly WatchlistRepositoryContract $watchlistRepository,
        private readonly MovieRepositoryContract $movieRepository,
        private readonly MovieApiProviderContract $movieProvider,
    ){}

    public function list(User $user, array $filters): LengthAwarePaginator
    {
        $watchlist = $this->watchlistRepository->firstOrCreateForUser($user);

        return $this->watchlistRepository->paginateMovies($watchlist, $filters);
    }

    public function add(User $user, array $data): Movie
    {
        [$movie, $providerName, $externalId, $inputTitle, $needsMetadata] = DB::transaction(function () use ($user, $data) {
            $watchlist    = $this->watchlistRepository->firstOrCreateForUser($user);
            $providerName = $this->movieProvider->providerName();

            [$movie, $externalId, $inputTitle] = $this->resolveMovie($data, $providerName);

            if ($this->watchlistRepository->hasMovie($watchlist, $movie->id)) {
                throw new MovieAlreadyInWatchlistException;
            }

            $needsMetadata = ! $this->movieRepository->hasMetadataForProvider($movie, $providerName);

            $this->watchlistRepository->attachMovie($watchlist, $movie->id, MovieStatus::ToWatch);

            return [
                $this->watchlistRepository->findMovieOrFail($watchlist, $movie->id),
                $providerName,
                $externalId,
                $inputTitle,
                $needsMetadata,
            ];
        });

        
        if ($needsMetadata) {
            MovieAddedToWatchlist::dispatch($movie, $providerName, $externalId, $inputTitle);
        }

        return $movie;
    }

    public function show(User $user, string $movieId): Movie
    {
        $watchlist = $this->watchlistRepository->firstOrCreateForUser($user);

        return $this->watchlistRepository->findMovieOrFail($watchlist, $movieId);
    }

    public function update(User $user, string $movieId, array $attributes): Movie
    {
        $watchlist = $this->watchlistRepository->firstOrCreateForUser($user);

        return $this->watchlistRepository->updatePivot($watchlist, $movieId, $attributes);
    }

    public function remove(User $user, string $movieId): void
    {
        $watchlist = $this->watchlistRepository->firstOrCreateForUser($user);

        $this->watchlistRepository->detachMovie($watchlist, $movieId);
    }

    private function resolveMovie(array $data, string $providerName): array
    {
        if (! empty($data['external_id'])) {
            $existing = $this->movieRepository->findExternalId($providerName, $data['external_id']);

            if ($existing) {
                return [$existing->movie, $data['external_id'], null];
            }

            $movie = $this->movieRepository->createWithExternalId($providerName, $data['external_id']);

            return [$movie, $data['external_id'], null];
        }

        $movie = $this->movieRepository->createWithTitle($data['title']);

        return [$movie, null, $data['title']];
    }
}
