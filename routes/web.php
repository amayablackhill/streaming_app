<?php

use App\Http\Controllers\ContentController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\Season;
use App\Models\Episode;
use App\Models\Content;
use App\Models\Genre;
use App\Http\Controllers\MovieController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/home', function () {
    $contents = Content::all();
    return view('content-list', compact('contents'));
});

Route::get('/dashboard', function () {
    //$contents = Content::all();
    return view('vueProject');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/movies/{id}', function ($id) {
    $content = Content::where('id', $id)->firstOrFail();
    return view('viewMovie', compact('content'));
});

Route::get('/series/{id}', function ($id) {
    $content = Content::where('id', $id)->firstOrFail();
    return view('viewSerie', compact('content'));
});

Route::get('/movies', function () {
    $contents = Content::all()->where('type', 'film');
    return view('content-list', compact('contents'));
})->name('content.movies.list');

Route::get('/series', function () {
    $contents = Content::all()->where('type', 'serie');
    return view('content-list', compact('contents'));
})->name('content.series.list');



Route::get('/admin/movies', function () {
    $movies = Content::all()->where('type', 'film');
    return view('moviesTable', compact('movies'));
})->name('movies.table');

Route::get('/admin/series', function () {
    $series = Content::all()->where('type', 'serie');
    return view('seriesTable', compact('series'));
})->name('series.table');




Route::get('/admin/addContent', function () {
    $genres = Genre::all();
    return view('addContent', compact('genres'));
})->name('content.add');

Route::post('/admin/addContent', [ContentController::class, 'addContent'])->name('content.add');

Route::get('/admin/editContent/{id}', function ($id) {
    $content = Content::findOrFail($id);
    $genres = Genre::all();
    return view('editContent', compact('content', 'genres'));
})->name('content.edit');

Route::put('/admin/editContent/{id}', [ContentController::class, 'updateContent'])->name('content.update');

Route::delete('/admin/deleteContent/{id}', [ContentController::class, 'destroyContent'])->name('content.destroy');


Route::get('/admin/addSeasons', function () {
    $series = Content::all()->where('type', 'serie');
    return view('addSeasons', compact('series'));
})->name('seasons.add');


Route::get('/admin/addSeasons/{id}', function ($id) {
    $serie = Content::findOrFail($id);
    return view('addSeason', compact('serie'));
})->name('seasons.edit');

Route::post('/admin/addSeasons', [ContentController::class, 'addSeasons'])->name('seasons.add');


// Rutas para temporadas
Route::get('/admin/series/{id}/seasons', function ($id) {
    $content = Content::with(['seasons.episodes'])->findOrFail($id);
    return view('seasonsManage', compact('content'));
})->name('seasons.manage');

Route::post('/admin/series/{id}/seasons', [ContentController::class, 'storeSeason'])->name('seasons.store');
    
// Rutas para episodios
Route::post('/admin/seasons/{id}/episodes', [ContentController::class, 'storeEpisode'])->name('episodes.store');

Route::delete('/admin/deleteSeason/{id}', [ContentController::class, 'destroySeason'])->name('seasons.destroy');

Route::get('/admin/seasons/{id}/episodes/create', function ($id) {
    $season = Season::findOrFail($id);
    return view('addEpisodes', compact('season'));
})->name('episodes.create');

Route::get('/admin/seasons/{id}/episodes/{episodeId}/edit', function ($id, $episodeId) {
    $episode = Episode::findOrFail($episodeId);
    $season = Season::findOrFail($id);
    return view('addEpisodes', compact('episode', 'season'));
})->name('episodes.edit');

Route::post('/admin/seasons/{id}/episodes/{episodeId}/edit', [ContentController::class, 'updateEpisode'])->name('episodes.update');


Route::get('/series/{id}/seasons/{seasonId}/episodes/{episodeId}/watch', function ($id, $seasonId, $episodeId) {
    $episode = Episode::findOrFail($episodeId);

    return view('watchEpisode', compact('episode'));
})->name('episodes.watch');


Route::prefix('admin')->group(function () {
    Route::fallback(function () {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard');
        }
        abort(404);
    });
});

// POSTMAN

Route::get('/admin/getMovies', function () {
    $movies = Content::all()->where('type', 'Film');
    return response()->json($movies);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/api/movies', function () {
    $movies = Content::where('type', 'film')->get();
    return response()->json(['movies' => $movies]);
});

Route::get('/api/series', function () {
    $series = Content::where('type', 'serie')->get();
    return response()->json(['series' => $series]);
});

Route::get('/footer', function() {
    return response()->json([
        'footer' => [
            'web' => config('app.url'),
            'address' => '123 Movie Street',
            'phone' => '+123456789',
            'email' => 'info@netflick.com'
        ]
    ]);
});



require __DIR__.'/auth.php';
