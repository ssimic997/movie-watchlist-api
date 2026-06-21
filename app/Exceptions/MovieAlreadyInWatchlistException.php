<?php

namespace App\Exceptions;

use RuntimeException;

class MovieAlreadyInWatchlistException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Movie is already in your watchlist.');
    }
}
