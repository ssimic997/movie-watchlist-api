<?php

namespace App\DTO;

class MovieApiResult
{
    public function __construct(
        public string  $externalId,
        public string  $title,
        public ?string $year = null,
        public ?string $rated = null,
        public ?string $runtime = null,
        public ?string $genre = null,
        public ?string $director = null,
        public ?string $writer = null,
        public ?string $actors = null,
        public ?string $plot = null,
        public ?string $posterUrl = null,
        public ?string $providerRating = null,
        public array   $raw = [],
    ) {}
}
