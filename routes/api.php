<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegistrationController;

Route::post('/auth/register', RegistrationController::class)->name('api.auth.register');
Route::post('/auth/login', LoginController::class)->name('api.auth.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/auth/logout', LogoutController::class)->name('api.auth.logout');
    Route::get("/user", function (Request $request) {
        return $request->user();
    });
});

