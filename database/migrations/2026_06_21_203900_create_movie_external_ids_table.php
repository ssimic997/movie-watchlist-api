<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movie_external_ids', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(\App\Models\Movie::class)
                ->constrained(
                    table: 'movies',
                    column: 'id',
                    indexName: 'fk_movie_external_ids_movies'
                )
                ->cascadeOnDelete()
            ;
            $table->string('provider');
            $table->string('external_id');
            $table->timestamps();

            $table->unique(['provider', 'external_id']);
            $table->index('movie_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_external_ids');
    }
};
