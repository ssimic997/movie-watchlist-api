<?php

namespace App\Events;

use App\Models\Movie;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MovieAddedToWatchlist
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Movie   $movie,
        public readonly string  $providerName,
        public readonly ?string $externalId = null,
        public readonly ?string $title = null,
    ) {}
}
