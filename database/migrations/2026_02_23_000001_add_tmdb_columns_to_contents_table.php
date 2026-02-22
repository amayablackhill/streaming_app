<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table): void {
            $table->unsignedBigInteger('tmdb_id')->nullable();
            $table->string('tmdb_type', 10)->nullable();
            $table->text('overview')->nullable();
            $table->unsignedInteger('runtime_minutes')->nullable();
            $table->decimal('rating_average', 4, 2)->nullable();
            $table->unsignedInteger('rating_count')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->string('youtube_trailer_id', 32)->nullable();
            $table->timestamp('tmdb_last_synced_at')->nullable();

            $table->unique(['tmdb_type', 'tmdb_id'], 'contents_tmdb_type_id_unique');
            $table->index('tmdb_type', 'contents_tmdb_type_index');
            $table->index('tmdb_last_synced_at', 'contents_tmdb_last_synced_index');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table): void {
            $table->dropUnique('contents_tmdb_type_id_unique');
            $table->dropIndex('contents_tmdb_type_index');
            $table->dropIndex('contents_tmdb_last_synced_index');

            $table->dropColumn([
                'tmdb_id',
                'tmdb_type',
                'overview',
                'runtime_minutes',
                'rating_average',
                'rating_count',
                'poster_path',
                'backdrop_path',
                'youtube_trailer_id',
                'tmdb_last_synced_at',
            ]);
        });
    }
};
