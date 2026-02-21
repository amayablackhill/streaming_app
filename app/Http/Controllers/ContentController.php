<?php

namespace App\Http\Controllers;

use App\Http\Requests\SeasonRequest;
use App\Models\Content;
use App\Models\Film;
use App\Models\Genre;
use App\Models\Serie;
use App\Models\Season;
use App\Models\Episode;
use App\Http\Requests\ContentRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ContentController extends Controller
{
    public function addContent(ContentRequest $request)
    {
        return DB::transaction(function () use ($request) {
            
            $content = Content::create([
                'title' => $request->title,
                'description' => $request->description,
                'release_date' => $request->release_date,
                'director' => $request->director,
                'genre_id' => $request->genre_id,
                'rating' => $request->rating ?? null,
                'duration' => $request->duration,
                'type' => $request->type,
                'picture' => $this->handleImageUpload($request),
                'video' => $this->handleVideoUpload($request),
            ]);

            return redirect()->route('content.add')->with('success', __('Content created successfully'));
        });
    }

    public function updateContent(ContentRequest $request, $id)
    {
        $content = Content::findOrFail($id);
        
        return DB::transaction(function () use ($request, $content) {

            $content->update([
                'title' => $request->title,
                'description' => $request->description,
                'release_date' => $request->release_date,
                'director' => $request->director,
                'genre_id' => $request->genre_id,
                'rating' => $request->rating ?? null,
                'type' => $content->type,
                'duration' => $request->duration,
                'picture' => $request->hasFile('picture') ? $this->handleImageUpload($request) : $content->picture,
                'video' => $request->hasFile('video') ? $this->handleVideoUpload($request) : $content->video,
            ]);

            return redirect()->route('dashboard')->with('success', __(':movie updated successfully', ['movie' => $content->title]));
        });
    }
    
    public function destroyContent($id)
    {
        $content = Content::findOrFail($id);

        $content->delete();
        
        return redirect()->route('dashboard')->with('success', __('Movie deleted successfully'));
    }

    public function destroySeason($id)
    {
        $season = Season::findOrFail($id);

        $season->delete();
        
        return back()->with('success', __('Movie deleted successfully'));
    }

    private function handleImageUpload($request)
    {

        if (!$request->hasFile('picture')) {
            return null;
        }

        $file = $request->file('picture');
        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();

        $where = $request->get('type') == 'film' ? 'movies' : ($request->get('type') == 'serie' ? 'series' : 'episodes');
        
        $path = $file->storeAs('public/'.$where, $filename);

        return $filename;
    }

    private function handleEpisodeImageUpload($request)
    {

        if (!$request->hasFile('cover_path')) {
            return null;
        }

        $file = $request->file('cover_path');
        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();

        $where = 'episodes';
        
        $path = $file->storeAs('public/'.$where, $filename);

        return $filename;
    }

    private function handleVideoUpload($request)
    {
        if (!$request->hasFile('video')) {
            return null;
        }
        
        $file = $request->file('video');
        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();

        $outputFilename = $filename;
        $outputFilenameBig = 'max_'.$filename;
        $outputFilenameMedium = 'mid_'.$filename;
        $outputFilenameSmall = 'min_'.$filename;

        // Almacenar el archivo ORIGINAL
        $path = $file->storeAs('content', $filename, 'public');
        $fullInputPath = storage_path('app/public/'.$path);
        
        // Ruta para el archivo COMPRIMIDO
        $outputPathBig = 'content/'.$outputFilenameBig;
        $outputPathMedium = 'content/'.$outputFilenameMedium;
        $outputPathSmall = 'content/'.$outputFilenameSmall;

        $fullOutputPathBig = storage_path('app/public/'.$outputPathBig);
        $fullOutputPathMedium = storage_path('app/public/'.$outputPathMedium);
        $fullOutputPathSmall = storage_path('app/public/'.$outputPathSmall);
        
        if (!file_exists(dirname($fullOutputPathBig))) {
            mkdir(dirname($fullOutputPathBig), 0755, true);
        }

        // Procesar el video
        $this->processVideo($fullInputPath, $fullOutputPathBig, 'max', $outputFilename);
        $this->processVideo($fullInputPath, $fullOutputPathMedium, 'mid');
        $this->processVideo($fullInputPath, $fullOutputPathSmall, 'min');
        
        return $outputFilename;
    }

    private function handleEpisodeUpload($request)
    {

        if (!$request->hasFile('path')) {
            return null;
        }
        
        $file = $request->file('path');
        $filename = time().'_'.uniqid().'_'.$request->episode_number.'_'.$request->title.'.'.$file->getClientOriginalExtension();

        $outputFilename = $filename;

        // Almacenar el archivo ORIGINAL
        $path = $file->storeAs('episodes', $filename, 'public');
        $fullInputPath = storage_path('app/public/'.$path);
              
        return $outputFilename;
    }

    private function processVideo($inputPath, $outputPath, $size, $filename = null)
    {
        $scale = 'scale=640:360';
        
        if ($size === 'mid') {
            $scale = 'scale=480:270';
        } elseif ($size === 'min') {
            $scale = 'scale=320:180';
        } else {
            if ($filename !== null) {
                $framesOutputDir = storage_path('app/public/content/frames/'.$filename);
                if (!file_exists($framesOutputDir)) {
                    mkdir($framesOutputDir, 0755, true);
                }

                // Obtener la duración del video para distribuir los frames
                $getDurationCommand = [
                    'ffprobe',
                    '-v', 'error',
                    '-show_entries', 'format=duration',
                    '-of', 'default=noprint_wrappers=1:nokey=1',
                    $inputPath
                ];
                
                $processDuration = new Process($getDurationCommand);
                $processDuration->run();
                $duration = (float)$processDuration->getOutput();
                
                $maxFrames = 10;
                
                
                $frameTimes = [];
                $timeInterval = $duration / ($maxFrames + 1);
                
                for ($i = 1; $i <= $maxFrames; $i++) {
                    $frameTime = $i * $timeInterval;
                    $frameTimes[] = $frameTime;
                    
                    $commandFrames = [
                        'ffmpeg',
                        '-ss', $frameTime,
                        '-i', $inputPath,
                        '-frames:v', '1',
                        '-q:v', '2',
                        $framesOutputDir.'/frame_'.str_pad($i, 3, '0', STR_PAD_LEFT).'.jpg'
                    ];
                    
                    $processFrames = new Process($commandFrames);
                    try {
                        $processFrames->mustRun();
                    } catch (ProcessFailedException $exception) {
                        throw new ProcessFailedException($processFrames);
                    }
                }
                
                file_put_contents($framesOutputDir.'/times.json', json_encode($frameTimes));
            }
        }
        
        $command = [
            'ffmpeg',
            '-i', $inputPath,
            '-vf', $scale,
            '-c:a', 'copy',
            '-y',
            $outputPath
        ];
        
        $process = new Process($command);
        
        $process->run();
        
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }



    // TEMPORADAS Y EPISODIOS


    public function storeSeason(Request $request, $id)
    {
        $request->validate([
            'season_number' => 'required|integer|min:1',
            'release_date' => 'required|date',
            'poster_path' => 'nullable|image|mimes:jpeg,png,jpg|max:51200',
            'overview' => 'nullable|string',
        ]);

        $content = Content::findOrFail($id);

        return DB::transaction(function () use ($request, $content, $id) {
            $season = Season::create([
                'serie_id' => $content->id,
                'season_number' => $request->season_number,
                'release_date' => $request->release_date,
                'poster_path' => $request->poster_path,
                'overview' => $request->overview
            ]);

            return redirect()->route('seasons.manage', $id)->with('success', 'Temporada añadida correctamente');

        });
        
    }

    public function updateEpisode(Request $request, $id, $episodeId)
    {
        $season = Season::findOrFail($id);

        $episode = Episode::findOrFail($episodeId);
        
        return DB::transaction(function () use ($request, $episode, $season) {

            $episode->update([
                'episode_number' => $request->episode_number,
                'title' => $request->title,
                'duration' => $request->duration,
                'release_date' => $request->release_date,
                'plot' => $request->plot,
                'cover_path' => $request->hasFile('cover_path') ? $this->handleImageUpload($request) : $episode->cover_path,
            ]);

            return redirect()->route('seasons.manage', $season->serie_id)->with('success', __(':episode updated successfully', ['episode' => $episode->title]));
        });
    }

    public function storeEpisode(Request $request, $id)
    {

        $request->validate([
            'episode_number' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'release_date' => 'required|date',
            'overview' => 'nullable|string',
            'plot' => 'nullable|string',
            'cover_path' => 'nullable|image|mimes:jpeg,png,jpg|max:51200',
            'episode_path' => 'nullable|mimetypes:video/mp4',
        ]);

        $season = Season::findOrFail($id);

        $episode = new Episode([
            'episode_number' => $request->episode_number,
            'title' => $request->title,
            'duration' => $request->duration,
            'release_date' => $request->release_date,
            'overview' => $request->overview,
            'plot' => $request->plot,
            'cover_path' => $this->handleEpisodeImageUpload($request),
            'episode_path' => $this->handleEpisodeUpload($request)
        ]);

        $season->episodes()->save($episode);

        return back()->with('success', 'Episodio añadido correctamente');
    }

    
    public function updateSeason(Request $request)
    {
        $season = Season::findOrFail($request->id);
        
        $season->update([
            'season_number' => $request->season_number,
            'release_date' => $request->release_date,
            'poster_path' => $request->poster_path,
            'overview' => $request->overview
        ]);

        return redirect()->route('seasons.manage', $season->serie->id)->with('success', 'Temporada actualizada correctamente');
    }

    public function show($id)
    {
        $content = Content::with('seasons.episodes')->findOrFail($id);

        return view('content.show', compact('content'));
    }


}