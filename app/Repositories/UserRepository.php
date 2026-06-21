<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryContract;
use App\Models\User;

class UserRepository implements UserRepositoryContract
{

    public function create(array $attributes): User
    {
        return User::query()->createQuietly($attributes);
    }
}
