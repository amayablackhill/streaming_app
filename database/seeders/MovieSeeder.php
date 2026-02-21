<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MovieSeeder extends Seeder
{

    use HasFactory;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $movies = [
            [
                'name' => "Inception",
                'description' => "Un ladrón especializado en el robo de secretos mediante el uso de sueños es contratado para realizar un trabajo casi imposible: plantar una idea en la mente de un CEO.",
                'release_year' => 2010,
                'director' => "Christopher Nolan",
                'genre' => "Sci-Fi, Action, Thriller",
                'rating' => 8.8,
                'picture' => "inception_poster.jpg"
            ],
            [
                'name' => "The Dark Knight",
                'description' => "Batman enfrenta a un villano conocido como el Joker, que tiene la intención de sumergir a Gotham City en el caos y poner a prueba la moralidad de la sociedad.",
                'release_year' => 2008,
                'director' => "Christopher Nolan",
                'genre' => "Action, Crime, Drama",
                'rating' => 9.0,
                'picture' => "dark_knight_poster.jpg"
            ],
            [
                'name' => "The Shawshank Redemption",
                'description' => "Un hombre injustamente condenado por asesinato forja una amistad con otro prisionero y encuentra la esperanza dentro de la prisión.",
                'release_year' => 1994,
                'director' => "Frank Darabont",
                'genre' => "Drama",
                'rating' => 9.3,
                'picture' => "shawshank_redemption_poster.jpg"
            ],
            [
                'name' => "The Matrix",
                'description' => "Un hacker descubre la verdad sobre la realidad que vive, entrando en un mundo de máquinas y humanos luchando por la libertad.",
                'release_year' => 1999,
                'director' => "Lana Wachowski, Lilly Wachowski",
                'genre' => "Sci-Fi, Action",
                'rating' => 8.7,
                'picture' => "matrix_poster.jpg"
            ],
            [
                'name' => "Forrest Gump",
                'description' => "Un hombre con un coeficiente intelectual bajo narra su extraordinaria vida, tocando temas históricos, personales y emocionales mientras recorre los eventos más importantes de los EE. UU.",
                'release_year' => 1994,
                'director' => "Robert Zemeckis",
                'genre' => "Drama, Comedy",
                'rating' => 8.8,
                'picture' => "forrest_gump_poster.jpg"
            ],
            [
                'name' => "Interstellar",
                'description' => "Un grupo de astronautas viaja a través de un agujero de gusano en busca de un nuevo hogar para la humanidad, mientras enfrentan el paso del tiempo y las complejidades del universo.",
                'release_year' => 2014,
                'director' => "Christopher Nolan",
                'genre' => "Sci-Fi, Drama, Adventure",
                'rating' => 8.6,
                'picture' => "interstellar_poster.jpg"
            ],
            [
                'name' => "Parasite",
                'description' => "Una familia pobre se infiltra en la vida de una familia rica, pero las cosas toman un giro inesperado que revela las desigualdades sociales y la lucha de clases.",
                'release_year' => 2019,
                'director' => "Bong Joon-ho",
                'genre' => "Drama, Comedy, Thriller",
                'rating' => 8.6,
                'picture' => "parasite_poster.jpg"
            ],
            [
                'name' => "Alien",
                'description' => "La nau de transport comercial USCSS Nostromo torna de Thedus a la Terra amb una tripulació de set membres en somni criogènic quan, en rebre una transmissió d'origen desconegut provinent d'un planetoide proper, l'ordinador central de la nau, 'Mare', els desperta. Degut a un mal aterratge la nau sofreix desperfectes i alguns membres de la tripulació es disposen a explorar l'origen de la senyal que resulta ser una antiga nau amb restes fossilitzades d'un gran alienígena assegut en la cadira del pilot. Un darrere l'altre, els tripulants seran víctimes d'una terrible bèstia que ha pujat a bord.",
                'release_year' => 1979,
                'director' => "Ridley Scott",
                'genre' => "Sci-Fi, Thriller, Horror",
                'rating' => 8.6,
                'picture' => "alien_poster.jpg"
            ],
        ];

        try {
            foreach ($movies as $movie) {
                Movie::create($movie);
            }
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}