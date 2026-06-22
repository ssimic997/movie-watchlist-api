<?php

namespace App\Jobs;

use App\Contracts\MovieApiProviderContract;
use App\Enum\MovieMetadataStatus;
use App\Exceptions\MovieNotFoundException;
use App\Models\Movie;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchMovieMetadataJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly Movie   $movie,
        public readonly string  $providerName,
        public readonly ?string $externalId = null,
        public readonly ?string $title = null,
    ) {
        $this->onQueue('fetch-movie-metadata');
    }

    public function uniqueId(): string
    {
        return "{$this->movie->id}:{$this->providerName}";
    }

    // Provider is injected by the container at run time, not serialized with the job.
    public function handle(MovieApiProviderContract $provider): void
    {
        try {
            $result = $this->externalId
                ? $provider->findByExternalId($this->externalId)
                : $provider->searchByTitle($this->title);
        } catch (MovieNotFoundException $e) {
            // Permanent failure — provider has no match. Record it per-provider so the
            // user can retry by re-adding the movie. Returning normally (not rethrowing)
            // tells the queue this job succeeded, so it won't be retried or written to
            // failed_jobs.
            $this->movie->metadata()->updateOrCreate(
                ['provider' => $this->providerName],
                ['metadata_status' => MovieMetadataStatus::FAILED],
            );

            return;
        }

        // Network / server errors from ->throw() still bubble up → retried up to $tries times.

        $this->movie->update(['title' => $result->title]);

        $this->movie->externalIds()->firstOrCreate([
            'provider'    => $this->providerName,
            'external_id' => $result->externalId,
        ]);

        $this->movie->metadata()->updateOrCreate(
            ['provider' => $this->providerName],
            [
                'metadata_status' => MovieMetadataStatus::SUCCESSFUL,
                'year'            => $result->year,
                'rated'           => $result->rated,
                'runtime'         => $result->runtime,
                'genre'           => $result->genre,
                'director'        => $result->director,
                'writer'          => $result->writer,
                'actors'          => $result->actors,
                'plot'            => $result->plot,
                'poster_url'      => $result->posterUrl,
                'provider_rating' => $result->providerRating,
                'raw_response'    => $result->raw,
            ]
        );
    }
}
