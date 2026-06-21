<?php

namespace App\Listeners;

use App\Events\MovieAddedToWatchlist;
use App\Jobs\FetchMovieMetadataJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DispatchFetchMetadataJob
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MovieAddedToWatchlist $event): void
    {
        FetchMovieMetadataJob::dispatch(
            $event->movie,
            $event->providerName,
            $event->externalId,
            $event->title,
        );
    }
}
