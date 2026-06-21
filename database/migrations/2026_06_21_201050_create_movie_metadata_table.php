<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const FK_MOVIE_METADATA_MOVIES = 'fk_movie_metadata_movies';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movie_metadata', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(\App\Models\Movie::class)
                ->constrained(
                    table: 'movies',
                    column: 'id',
                    indexName: self::FK_MOVIE_METADATA_MOVIES
                )
                ->cascadeOnDelete()
            ;
            $table->string('provider');
            $table->string('year')->nullable();
            $table->string('rated')->nullable();
            $table->string('runtime')->nullable();
            $table->string('genre')->nullable();
            $table->string('director')->nullable();
            $table->text('writer')->nullable();
            $table->text('actors')->nullable();
            $table->text('plot')->nullable();
            $table->string('poster_url')->nullable();
            $table->string('provider_rating')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->unique(['movie_id', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_metadata');
    }
};
