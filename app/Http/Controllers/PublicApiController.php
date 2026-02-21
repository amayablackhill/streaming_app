<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicApiController extends Controller
{
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function movies(): JsonResponse
    {
        $movies = Content::where('type', 'film')->get();

        return response()->json(['movies' => $movies]);
    }

    public function series(): JsonResponse
    {
        $series = Content::where('type', 'serie')->get();

        return response()->json(['series' => $series]);
    }

    public function footer(): JsonResponse
    {
        return response()->json([
            'footer' => [
                'web' => config('app.url'),
                'address' => '123 Movie Street',
                'phone' => '+123456789',
                'email' => 'info@netflick.com',
            ],
        ]);
    }
}
