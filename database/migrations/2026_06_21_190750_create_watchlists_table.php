<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    const NAME_MAX_LENGTH = 255;
    const FK_WATCHLIST_USERS = 'fk_watchlist_users';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('watchlists', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name', self::NAME_MAX_LENGTH);
            $table->foreignIdFor(\App\Models\User::class, 'user_id')
                ->constrained(
                    table: 'users',
                    column: 'id',
                    indexName: self::FK_WATCHLIST_USERS
                )
                ->cascadeOnDelete()
            ;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchlists');
    }
};
