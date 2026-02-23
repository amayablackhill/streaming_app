<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curated_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('curated_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curated_list_id')->constrained('curated_lists')->cascadeOnDelete();
            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->integer('rank');
            $table->timestamps();

            $table->unique(['curated_list_id', 'content_id'], 'curated_list_items_list_content_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curated_list_items');
        Schema::dropIfExists('curated_lists');
    }
};

