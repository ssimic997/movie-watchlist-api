<?php

namespace App\Services\MovieApi;

use App\DTO\MovieApiResult;
use App\Exceptions\MovieNotFoundException;
use Illuminate\Http\Client\Factory as HttpClient;

class OmdbMovieApiProvider implements \App\Contracts\MovieApiProviderContract
{
    private const BASE_URL = 'https://www.omdbapi.com/';

    public function __construct(private readonly HttpClient $http) {}

    public function findByExternalId(string $externalId): MovieApiResult
    {
        $response = $this->http->get(self::BASE_URL, [
            'apikey' => config('movie-api.providers.omdb.api_key'),
            'i'      => $externalId,
            'type'   => 'movie',
        ])->throw()->json();

        if (($response['Response'] ?? 'True') === 'False') {
            throw new MovieNotFoundException($externalId);
        }

        return $this->toResult($response);
    }

    public function searchByTitle(string $title): MovieApiResult
    {

        $response = $this->http->get(self::BASE_URL, [
            'apikey' => config('movie-api.providers.omdb.api_key'),
            't'      => $title,
            'type'   => 'movie',
        ])->throw()->json();

        if (($response['Response'] ?? 'True') === 'False') {
            throw new MovieNotFoundException($title);
        }

        return $this->toResult($response);
    }

    public function providerName(): string
    {
        return 'omdb';
    }

    private function toResult(array $data): MovieApiResult
    {
        return new MovieApiResult(
            externalId:     $data['imdbID'] ?? '',
            title:          $data['Title'] ?? '',
            year:           $data['Year'] ?? null,
            rated:          $data['Rated'] ?? null,
            runtime:        $data['Runtime'] ?? null,
            genre:          $data['Genre'] ?? null,
            director:       $data['Director'] ?? null,
            writer:         $data['Writer'] ?? null,
            actors:         $data['Actors'] ?? null,
            plot:           $data['Plot'] ?? null,
            posterUrl:      $data['Poster'] !== 'N/A' ? ($data['Poster'] ?? null) : null,
            providerRating: $data['imdbRating'] ?? null,
            raw:            $data,
        );
    }
}
