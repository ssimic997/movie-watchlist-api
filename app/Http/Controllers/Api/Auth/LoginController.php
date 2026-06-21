<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(private readonly AuthService $authService){}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->login($request->only('email', 'password'));

        return response()->json(['token' => $token]);
    }
}
