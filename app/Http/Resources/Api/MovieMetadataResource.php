<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieMetadataResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'provider'        => $this->provider,
            'metadata_status' => $this->metadata_status?->value,
            'year'            => $this->year,
            'rated'           => $this->rated,
            'runtime'         => $this->runtime,
            'genre'           => $this->genre,
            'director'        => $this->director,
            'writer'          => $this->writer,
            'actors'          => $this->actors,
            'plot'            => $this->plot,
            'poster_url'      => $this->poster_url,
            'provider_rating' => $this->provider_rating,
        ];
    }
}
