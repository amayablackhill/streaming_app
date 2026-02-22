<?php

use App\Http\Controllers\AdminPageController;
use App\Http\Controllers\Admin\TmdbImportController;
use App\Http\Controllers\AdminContentController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SeasonEpisodeController;
use App\Http\Controllers\VideoAssetController;
use App\Http\Controllers\VideoPipelineHealthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::permanentRedirect('/home', '/');
Route::permanentRedirect('/dashboard', '/')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/movies/{id}', [CatalogController::class, 'showMovie']);
Route::get('/series/{id}', [CatalogController::class, 'showSeries']);

Route::get('/movies', [CatalogController::class, 'movies'])->name('content.movies.list');
Route::get('/series', [CatalogController::class, 'series'])->name('content.series.list');
Route::get('/search', [CatalogController::class, 'search'])->name('search');

Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminPageController::class, 'index'])->name('admin.home');
    Route::get('/movies', [AdminPageController::class, 'moviesTable'])->name('movies.table');
    Route::get('/series', [AdminPageController::class, 'seriesTable'])->name('series.table');

    Route::get('/addContent', [AdminPageController::class, 'addContentForm'])->name('content.add');
    Route::post('/addContent', [AdminContentController::class, 'addContent'])->name('content.add');

    Route::get('/editContent/{id}', [AdminPageController::class, 'editContentForm'])->name('content.edit');
    Route::put('/editContent/{id}', [AdminContentController::class, 'updateContent'])->name('content.update');
    Route::delete('/deleteContent/{id}', [AdminContentController::class, 'destroyContent'])->name('content.destroy');

    Route::get('/addSeasons', [AdminPageController::class, 'addSeasonsIndex'])->name('seasons.add');
    Route::get('/series/{id}/seasons', [AdminPageController::class, 'seasonsManage'])->name('seasons.manage');
    Route::post('/series/{id}/seasons', [SeasonEpisodeController::class, 'storeSeason'])->name('seasons.store');

    Route::post('/seasons/{id}/episodes', [SeasonEpisodeController::class, 'storeEpisode'])->name('episodes.store');
    Route::delete('/deleteSeason/{id}', [SeasonEpisodeController::class, 'destroySeason'])->name('seasons.destroy');
    Route::get('/seasons/{id}/episodes/create', [AdminPageController::class, 'createEpisodeForm'])->name('episodes.create');
    Route::get('/seasons/{id}/episodes/{episodeId}/edit', [AdminPageController::class, 'editEpisodeForm'])->name('episodes.edit');
    Route::post('/seasons/{id}/episodes/{episodeId}/edit', [SeasonEpisodeController::class, 'updateEpisode'])->name('episodes.update');
    Route::get('/video-assets/{videoAsset}', [VideoAssetController::class, 'show'])->name('video-assets.show');
    Route::get('/video-assets/{videoAsset}/status', [VideoAssetController::class, 'status'])->name('video-assets.status');
    Route::get('/health/video-pipeline', VideoPipelineHealthController::class)->name('admin.health.video-pipeline');
    Route::get('/tmdb/search', [TmdbImportController::class, 'search'])->name('admin.tmdb.search');
    Route::post('/tmdb/import', [TmdbImportController::class, 'import'])->name('admin.tmdb.import');

    Route::fallback([AdminPageController::class, 'fallback']);
});

Route::get('/series/{id}/seasons/{seasonId}/episodes/{episodeId}/watch', [CatalogController::class, 'watchEpisode'])->name('episodes.watch');

require __DIR__.'/auth.php';
