<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Content;

// Obtener todas las películas
Route::get('/movies', function () {
    $movies = Content::where('type', 'film')->get();
    return response()->json(['movies' => $movies]);
});

// Buscar películas por título
Route::get('/search/{query}', function ($query) {
    $movies = Content::where('type', 'film')
        ->where('title', 'LIKE', "%{$query}%")
        ->get();

    return response()->json(['movies' => $movies]);
});

// Obtener los datos de una película específica
Route::get('/movies/{id}', function ($id) {
    $movie = Content::where('type', 'film')->findOrFail($id);
    return response()->json(['movie' => $movie]);
});

// Actualizar los datos de una película
Route::put('/movies/{id}', function (Request $request, $id) {
    $movie = Content::where('type', 'film')->findOrFail($id);
    $movie->update($request->all());

    return response()->json(['message' => 'Película actualizada con éxito']);
});
