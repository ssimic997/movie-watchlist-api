<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\MovieAlreadyInWatchlistException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddMovieRequest;
use App\Http\Requests\Api\ListWatchlistMoviesRequest;
use App\Http\Requests\Api\UpdateWatchlistMovieRequest;
use App\Http\Resources\Api\WatchlistMovieResource;
use App\Services\WatchlistMovieService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WatchlistMovieController extends Controller
{
    public function __construct(private readonly WatchlistMovieService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ListWatchlistMoviesRequest $request): AnonymousResourceCollection
    {
        $movies = $this->service->list($request->user(), $request->filters());

        return WatchlistMovieResource::collection($movies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AddMovieRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $movie = $this->service->add($request->user(), $request->validated());
        } catch (MovieAlreadyInWatchlistException) {
            return response()->json(['message' => 'Movie is already in your watchlist.'], 409);
        }

        return (new WatchlistMovieResource($movie))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $movieId): JsonResponse
    {
        $movie = $this->service->findMovieById($request->user(), $movieId);

        if (! $movie) {
            return response()->json(['message' => 'Movie not found in your watchlist.'], 404);
        }

        return (new WatchlistMovieResource($movie))->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWatchlistMovieRequest $request, string $movieId): JsonResponse
    {
        $movie = $this->service->update($request->user(), $movieId, $request->validated());

        return (new WatchlistMovieResource($movie))->response();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $movieId): JsonResponse
    {
        $this->service->remove($request->user(), $movieId);

        return response()->json(null, 204);
    }
}
