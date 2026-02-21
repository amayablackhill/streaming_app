<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    public function run()
    {
        $genres = [
            'Action', 'Adventure', 'Comedy', 'Drama', 'Fantasy',
            'Terror', 'Sci-Fi', 'Romance', 'Thriller', 'Documentary'
        ];

        foreach ($genres as $genre) {
            Genre::create(['name' => $genre]);
        }
    }
}