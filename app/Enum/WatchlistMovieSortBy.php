<?php

namespace App\Enum;

enum WatchlistMovieSortBy: string
{
    case Status = 'status';
    case Title  = 'title';

    public function column(): string
    {
        return match($this) {
            self::Status => 'watchlist_movies.status',
            self::Title  => 'movies.title',
        };
    }
}
