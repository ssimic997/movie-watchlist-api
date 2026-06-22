<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    const MOVIE_STATUS_COLUMN_NAME = 'value';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('watchlist_movies', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Watchlist::class)
                ->constrained(
                    table: 'watchlists',
                    column: 'id',
                    indexName: 'fk_watchlist_movies_watchlist')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;
            $table->foreignIdFor(\App\Models\Movie::class)
                ->constrained(
                    table: 'movies',
                    column: 'id',
                    indexName: 'fk_watchlist_movies_movies'
                )
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;
            $table->primary(['watchlist_id', 'movie_id'], 'pk_watchlist_movies');
            $table->enum(
                'status',
                array_column(
                    \App\Enum\MovieStatus::cases(),
                    self::MOVIE_STATUS_COLUMN_NAME
                )
            );
            $table->unsignedTinyInteger('user_rating')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchlist_movies');
    }
};
