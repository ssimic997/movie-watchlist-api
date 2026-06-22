<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegistrationController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\WatchlistMovieController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', RegistrationController::class)->name('api.auth.register');
Route::post('/auth/login', LoginController::class)->name('api.auth.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/auth/logout', LogoutController::class)->name('api.auth.logout');
    Route::get("/user", function (Request $request) {
        return $request->user();
    });

    Route::prefix('watchlist/movies')->group(function () {
        Route::get('/', [WatchlistMovieController::class, 'index'])->name('api.watchlist.index');
        Route::post('/', [WatchlistMovieController::class, 'store'])->name('api.watchlist.store');
        Route::get('/{movieId}', [WatchlistMovieController::class, 'show'])->name('api.watchlist.show');
        Route::patch('/{movieId}', [WatchlistMovieController::class, 'update'])->name('api.watchlist.update');
        Route::delete('/{movieId}', [WatchlistMovieController::class, 'destroy'])->name('api.watchlist.destroy');
    });

    Route::prefix('/movies')->group(function () {
        Route::patch('/{movieId}/metadata', [MovieController::class, 'refetchMetadata'])->name('api.movie.metadata.update');
    });
});

