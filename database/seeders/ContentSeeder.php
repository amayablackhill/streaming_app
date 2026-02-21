<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Content;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContentSeeder extends Seeder
{
    use HasFactory;


    public function run()
    {
        $movies = [
            [
                'title' => "Inception",
                'description' => "Un ladrón especializado en el robo de secretos mediante el uso de sueños es contratado para realizar un trabajo casi imposible: plantar una idea en la mente de un CEO.",
                'release_date' => Carbon::create(2010, 7, 16),
                'duration' => 148, 
                'rating' => 88,
                'genre_id' => 7,
                'director' => "Christopher Nolan",
                'type' => 'film',
                'video' => "sample.mp4",
                'picture' => "inception_poster.jpg"
            ],
            [
                'title' => "The Dark Knight",
                'description' => "Batman enfrenta a un villano conocido como el Joker, que tiene la intención de sumergir a Gotham City en el caos y poner a prueba la moralidad de la sociedad.",
                'release_date' => Carbon::create(2008, 7, 18),
                'duration' => 152,
                'rating' => 90,
                'genre_id' => 1,
                'director' => "Christopher Nolan",
                'type' => 'film',
                'video' => "sample.mp4",
                'picture' => "dark_knight_poster.jpg"
            ],
            [
                'title' => "The Shawshank Redemption",
                'description' => "Un hombre injustamente condenado por asesinato forja una amistad con otro prisionero y encuentra la esperanza dentro de la prisión.",
                'release_date' => Carbon::create(1994, 9, 23),
                'duration' => 142,
                'rating' => 93,
                'genre_id' => 4,
                'director' => "Frank Darabont",
                'type' => 'film',
                'video' => "sample.mp4",
                'picture' => "shawshank_redemption_poster.jpg"
            ],
            [
                'title' => "The Matrix",
                'description' => "Un hacker descubre la verdad sobre la realidad que vive, entrando en un mundo de máquinas y humanos luchando por la libertad.",
                'release_date' => Carbon::create(1999, 3, 31),
                'duration' => 136,
                'rating' => 87,
                'genre_id' => 7,
                'director' => "Lana Wachowski, Lilly Wachowski",
                'type' => 'film',
                'video' => "sample.mp4",
                'picture' => "matrix_poster.jpg"
            ],
            [
                'title' => "Forrest Gump",
                'description' => "Un hombre con un coeficiente intelectual bajo narra su extraordinaria vida, tocando temas históricos, personales y emocionales mientras recorre los eventos más importantes de los EE. UU.",
                'release_date' => Carbon::create(1994, 7, 6),
                'duration' => 142,
                'rating' => 88,
                'genre_id' => 4,
                'director' => "Robert Zemeckis",
                'type' => 'film',
                'video' => "sample.mp4",
                'picture' => "forrest_gump_poster.jpg"
            ],
            [
                'title' => "Interstellar",
                'description' => "Un grupo de astronautas viaja a través de un agujero de gusano en busca de un nuevo hogar para la humanidad, mientras enfrentan el paso del tiempo y las complejidades del universo.",
                'release_date' => Carbon::create(2014, 11, 7),
                'duration' => 169,
                'rating' => 86,
                'genre_id' => 7,
                'director' => "Christopher Nolan",
                'type' => 'film',
                'video' => "sample.mp4",
                'picture' => "interstellar_poster.jpg"
            ],
            [
                'title' => "Parasite",
                'description' => "Una familia pobre se infiltra en la vida de una familia rica, pero las cosas toman un giro inesperado que revela las desigualdades sociales y la lucha de clases.",
                'release_date' => Carbon::create(2019, 5, 30),
                'duration' => 132,
                'rating' => 86,
                'genre_id' => 9,
                'director' => "Bong Joon-ho",
                'type' => 'film',
                'video' => "sample.mp4",
                'picture' => "parasite_poster.jpg"
            ],
            [
                'title' => "Alien",
                'description' => "La tripulación de una nave espacial comercial se enfrenta a una terrorífica criatura alienígena que ha entrado a bordo.",
                'release_date' => Carbon::create(1979, 5, 25),
                'duration' => 117,
                'rating' => 86,
                'genre_id' => 6,
                'director' => "Ridley Scott",
                'type' => 'film',
                'video' => "sample.mp4",
                'picture' => "alien_poster.jpg"
            ],
            [
                'title' => "SERIE 1",
                'description' => "Una familia pobre se infiltra en la vida de una familia rica, pero las cosas toman un giro inesperado que revela las desigualdades sociales y la lucha de clases.",
                'release_date' => Carbon::create(2019, 5, 30),
                'duration' => 132,
                'rating' => 86,
                'genre_id' => 9,
                'director' => "Bong Joon-ho",
                'type' => 'serie',
                'video' => "sample.mp4",
                'picture' => "parasite_poster.jpg"
            ],
            [
                'title' => "SERIE 2",
                'description' => "Una familia pobre se infiltra en la vida de una familia rica, pero las cosas toman un giro inesperado que revela las desigualdades sociales y la lucha de clases.",
                'release_date' => Carbon::create(2019, 5, 30),
                'duration' => 132,
                'rating' => 86,
                'genre_id' => 9,
                'director' => "Bong Joon-ho",
                'type' => 'serie',
                'video' => "sample.mp4",
                'picture' => "alien_poster.jpg"
            ],
        ];

        try {
            foreach ($movies as $movie) {
                Content::create($movie);
            }
            $this->command->info('¡Películas insertadas correctamente!');
        } catch (\Exception $e) {
            $this->command->error('Error al insertar películas: ' . $e->getMessage());
        }
    }
}