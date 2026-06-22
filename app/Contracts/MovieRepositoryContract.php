<?php

namespace App\Contracts;

use App\Models\Movie;
use App\Models\MovieExternalId;

interface MovieRepositoryContract
{
    public function findExternalId(string $provider, string $externalId): ?MovieExternalId;

    public function createWithExternalId(string $provider, string $externalId): Movie;

    public function createWithTitle(string $title): Movie;

    public function hasMetadataForProvider(Movie $movie, string $provider): bool;

    public function findById(string $movieId): Movie;

}
