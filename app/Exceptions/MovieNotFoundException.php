<?php

namespace App\Exceptions;

use RuntimeException;

class MovieNotFoundException extends RuntimeException
{
    public function __construct(string $identifier)
    {
        parent::__construct("Movie not found: {$identifier}");
    }
}
