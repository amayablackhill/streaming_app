<?php

namespace Database\Seeders;

use App\Models\Season;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeasonSeeder extends Seeder
{
    use HasFactory;


    public function run()
    {
        $seasons = [
            [
                'serie_id' => 1,
                'season_number' => 1,
                'release_date' => Carbon::now(),
                'poster_path' => '',
                'overview' => 'overview'
            ],
            [
                'serie_id' => 1,
                'season_number' => 2,
                'release_date' => Carbon::now(),
                'poster_path' => '',
                'overview' => 'overview'
            ],
            [
                'serie_id' => 1,
                'season_number' => 3,
                'release_date' => Carbon::now(),
                'poster_path' => '',
                'overview' => 'overview'
            ],
            [
                'serie_id' => 2,
                'season_number' => 1,
                'release_date' => Carbon::now(),
                'poster_path' => '',
                'overview' => 'overview'
            ],
        ];

        try {
            foreach ($seasons as $season) {
                Season::create($season);
            }
            $this->command->info('Temporadas insertadas correctamente!');
        } catch (\Exception $e) {
            $this->command->error('Error al insertar temporadas: ' . $e->getMessage());
        }
    }
}