<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table): void {
            $table->unsignedBigInteger('tmdb_id')->nullable()->after('serie_id');
            $table->unsignedInteger('episode_count')->nullable()->after('season_number');
            $table->timestamp('tmdb_last_synced_at')->nullable()->after('overview');

            $table->unique('tmdb_id', 'seasons_tmdb_id_unique');
            $table->index('tmdb_last_synced_at', 'seasons_tmdb_last_synced_index');
        });

        Schema::table('episodes', function (Blueprint $table): void {
            $table->unsignedBigInteger('tmdb_id')->nullable()->after('season_id');
            $table->unsignedSmallInteger('runtime_minutes')->nullable()->after('duration');
            $table->string('still_path')->nullable()->after('cover_path');
            $table->timestamp('tmdb_last_synced_at')->nullable()->after('still_path');

            $table->unique('tmdb_id', 'episodes_tmdb_id_unique');
            $table->index('tmdb_last_synced_at', 'episodes_tmdb_last_synced_index');
        });
    }

    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table): void {
            $table->dropUnique('episodes_tmdb_id_unique');
            $table->dropIndex('episodes_tmdb_last_synced_index');
            $table->dropColumn([
                'tmdb_id',
                'runtime_minutes',
                'still_path',
                'tmdb_last_synced_at',
            ]);
        });

        Schema::table('seasons', function (Blueprint $table): void {
            $table->dropUnique('seasons_tmdb_id_unique');
            $table->dropIndex('seasons_tmdb_last_synced_index');
            $table->dropColumn([
                'tmdb_id',
                'episode_count',
                'tmdb_last_synced_at',
            ]);
        });
    }
};
