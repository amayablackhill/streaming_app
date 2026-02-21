<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('seasons');
            $table->integer('episode_number');
            $table->string('title', 255);
            $table->integer('duration'); // en minutos
            $table->date('release_date');
            $table->text('plot')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('episode_path')->nullable();
            $table->timestamps();
            
            $table->unique(['season_id', 'episode_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('episodes');
    }
};