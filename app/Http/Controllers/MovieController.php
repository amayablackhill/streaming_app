<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use App\Http\Requests\MovieRequest;

class MovieController extends Controller
{

    public function addMovie(MovieRequest $request) {
        $movie = new Movie();
        $movie->name = $request->name;
        $movie->description = $request->description;
        $movie->release_year = $request->release_year;
        $movie->director = $request->director;
        $movie->genre = $request->genre;
        $movie->rating = $request->rating;
        
        if ($request->hasFile('picture')) {
            $image = $request->file('picture');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            
            $path = $image->storeAs('public/movies', $imageName);
            
            $movie->picture = $imageName;
        }
        
        $movie->save();
    }
}
