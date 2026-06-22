<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            [
                "email" => "test@example.com",
            ],
            [
                "email"  => "test@example.com",
                "name" => "Test User",
                "password" => Hash::make("password"),
                "email_verified_at" => Carbon::now(),
            ]
        );

        User::query()->firstOrCreate(
            [
                "email" => "test-postman@example.com",
            ],
            [
                "email"  => "test-postman@example.com",
                "name" => "Test Postman User",
                "password" => Hash::make("password"),
                "email_verified_at" => Carbon::now(),
            ]
        );
    }
}
