<?php

namespace App\Providers;

use App\Contracts\MovieRepositoryContract;
use App\Contracts\UserRepositoryContract;
use App\Contracts\WatchlistRepositoryContract;
use App\Repositories\MovieRepository;
use App\Repositories\UserRepository;
use App\Repositories\WatchlistRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(UserRepositoryContract::class, UserRepository::class);
        $this->app->singleton(MovieRepositoryContract::class, MovieRepository::class);
        $this->app->singleton(WatchlistRepositoryContract::class, WatchlistRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
