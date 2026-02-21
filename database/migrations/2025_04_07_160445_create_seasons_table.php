<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('serie_id')->constrained('contents');
            $table->integer('season_number');
            $table->date('release_date');
            $table->string('poster_path')->nullable();
            $table->text('overview')->nullable();
            $table->timestamps();
            
            $table->unique(['serie_id', 'season_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('seasons');
    }
};