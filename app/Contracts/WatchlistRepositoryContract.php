<?php

namespace App\Contracts;

use App\Enum\MovieStatus;
use App\Models\Movie;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface WatchlistRepositoryContract
{

    public function firstOrCreateForUser(User $user): Watchlist;

    public function paginateMovies(Watchlist $watchlist, array $filters): LengthAwarePaginator;

    public function findMovieOrFail(Watchlist $watchlist, string $movieId): Movie;

    public function hasMovie(Watchlist $watchlist, string $movieId): bool;

    public function attachMovie(Watchlist $watchlist, string $movieId, MovieStatus $status): void;

    public function updatePivot(Watchlist $watchlist, string $movieId, array $attributes): Movie;

    public function detachMovie(Watchlist $watchlist, string $movieId): void;

}
