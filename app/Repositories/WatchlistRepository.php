<?php

namespace App\Repositories;

use App\Contracts\WatchlistRepositoryContract;
use App\Models\Movie;
use App\Models\Watchlist;
use App\Enum\MovieStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WatchlistRepository implements WatchlistRepositoryContract
{

    public function firstOrCreateForUser(\App\Models\User $user): Watchlist
    {
        return $user->watchlists()->firstOrCreate([], ['name' => "{$user->name}'s Watchlist"]);
    }

    public function paginateMovies(Watchlist $watchlist, array $filters): LengthAwarePaginator
    {
        return $watchlist->movies()
            ->with(['externalIds', 'metadata'])
            ->when($filters['status'], fn ($q) => $q->where('watchlist_movies.status', $filters['status']))
            ->when($filters['search'], function ($q) use ($filters) {
                $escaped = addcslashes($filters['search'], '%_\\');
                $q->where('movies.title', 'ilike', "%{$escaped}%");
            })
            ->orderBy($filters['sort_by']->column(), $filters['sort_dir'])
            ->paginate($filters['per_page']);
    }

    public function findMovieOrFail(Watchlist $watchlist, string $movieId): Movie
    {
        /** @var Movie $movie */
        $movie = $watchlist->movies()
            ->where('movies.id', $movieId)
            ->firstOrFail();

        $movie->load(['externalIds', 'metadata']);

        return $movie;
    }

    public function hasMovie(Watchlist $watchlist, string $movieId): bool
    {
        return $watchlist->movies()->where('movie_id', $movieId)->exists();
    }

    public function attachMovie(Watchlist $watchlist, string $movieId, MovieStatus $status): void
    {
        $watchlist->movies()->attach($movieId, ['status' => $status->value]);
    }

    public function updatePivot(Watchlist $watchlist, string $movieId, array $attributes): Movie
    {
        $watchlist->movies()->where('movies.id', $movieId)->firstOrFail();
        $watchlist->movies()->updateExistingPivot($movieId, $attributes);

        $movie = $watchlist->movies()->where('movies.id', $movieId)->first();
        $movie->load(['externalIds', 'metadata']);

        return $movie;
    }

    public function detachMovie(Watchlist $watchlist, string $movieId): void
    {
        $movieExists = $watchlist->movies()->where('movies.id', $movieId)->exists();

        if (! $movieExists) {
            return;
        }

        $watchlist->movies()->detach($movieId);
    }

    public function findMovieById(Watchlist $watchlist, string $movieId): Movie | null
    {
        $movie = $watchlist->movies()
            ->where('movies.id', $movieId)
            ->first();

        if (! $movie) {
            return null;
        }

        $movie->load(['externalIds', 'metadata']);

        return $movie;
    }
}
