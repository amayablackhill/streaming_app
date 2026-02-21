<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            
            $table->string('title', 255);
            $table->foreignId('genre_id')->constrained('genres');
            $table->date('release_date');
            $table->integer('duration');
            $table->integer('rating')->nullable();
            $table->text('description');
            $table->text('type');
            
            $table->string('director')->nullable();
            $table->string('picture')->nullable();
            $table->string('video')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contents');
    }
};