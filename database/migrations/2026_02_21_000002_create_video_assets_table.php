<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('content_id')->nullable()->constrained('contents')->nullOnDelete();

            $table->string('original_filename')->nullable();
            $table->string('source_disk')->default('public');
            $table->string('source_path');
            $table->string('hls_disk')->default('public');
            $table->string('hls_master_path')->nullable();
            $table->string('thumbnails_path')->nullable();

            $table->string('status', 20)->default('pending');
            $table->decimal('duration_seconds', 8, 2)->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('video_bitrate')->nullable();
            $table->json('meta')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_assets');
    }
};
