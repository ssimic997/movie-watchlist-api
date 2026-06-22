<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MovieService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function __construct(private MovieService $service){}

    public function refetchMetadata(Request $request, string $movieId): JsonResponse
    {
        $this->service->refetchMetadata($movieId);

        return response()->json(null, 202);
    }
}
