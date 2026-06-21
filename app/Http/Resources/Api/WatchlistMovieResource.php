<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class WatchlistMovieResource extends JsonApiResource
{
    public function toArray(Request $request): array
    {
        $pivot = $this->pivot;

        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'status'       => $pivot?->status?->value,
            'user_rating'  => $pivot?->user_rating,
            'notes'        => $pivot?->notes,
            'added_at'     => $pivot?->created_at?->toISOString(),
            'external_ids' => $this->whenLoaded('externalIds', fn() =>
            $this->externalIds->map(fn($e) => [
                'provider'    => $e->provider,
                'external_id' => $e->external_id,
            ])
            ),
            'metadata'     => $this->whenLoaded('metadata', fn() =>
            MovieMetadataResource::collection($this->metadata)
            ),
        ];
    }
}
