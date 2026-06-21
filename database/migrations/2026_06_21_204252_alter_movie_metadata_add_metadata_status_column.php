<?php

use App\Enum\MovieMetadataStatus;
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
        Schema::table('movie_metadata', function (Blueprint $table) {
            $table->enum('metadata_status', array_column(MovieMetadataStatus::cases(), 'value'))
                ->default(MovieMetadataStatus::PENDING)->after('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movie_metadata', function (Blueprint $table) {
            $table->dropColumn('metadata_status');
        });
    }
};
