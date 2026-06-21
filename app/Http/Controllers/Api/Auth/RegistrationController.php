<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Api\RegistrationRequest;

class RegistrationController extends Controller
{
    public function __construct(private readonly AuthService $authService){}

    public function __invoke(RegistrationRequest $request): JsonResponse
    {
        $token = $this->authService->register([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['token' => $token], 201);
    }
}
