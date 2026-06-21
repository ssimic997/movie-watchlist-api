<?php

namespace App\Services;

use App\Contracts\UserRepositoryContract;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthService
{
    public function __construct(private readonly UserRepositoryContract $userRepository) {}

    public function login(array $credentials): string
    {
        if (! Auth::attempt($credentials)) {
            throw new UnauthorizedHttpException('', 'Invalid credentials.');
        }

        /** @var User $user */
        $user = Auth::user();
        $user->tokens()->delete();

        return $user->createToken('api')->plainTextToken;

    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function register(array $attributes)
    {
        $user = $this->userRepository->create($attributes);

        return $user->createToken('api')->plainTextToken;
    }
}
