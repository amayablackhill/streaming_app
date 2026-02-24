<?php

namespace App\Http\Requests;

use App\Models\Content;
use Illuminate\Foundation\Http\FormRequest;

class ContentRequest extends FormRequest
{
    public function authorize()
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        if ($this->routeIs('content.update')) {
            $contentId = $this->route('id');
            $content = $contentId ? Content::find($contentId) : null;

            return $content ? $user->can('update', $content) : $user->can('create', Content::class);
        }

        return (bool) $user->can('create', Content::class);
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'release_date' => 'required|date',
            'duration' => 'required|integer|min:1',
            'director' => 'required|string|max:255',
            'genre_id' => 'required|exists:genres,id',
            'rating' => 'nullable|numeric|between:0,100',
            'type' => 'required|string|in:film,serie',
            'is_featured' => 'nullable|boolean',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg|max:51200',
            'poster_image' => 'nullable|image|mimes:jpeg,png,jpg|max:51200',
            'backdrop_image' => 'nullable|image|mimes:jpeg,png,jpg|max:51200',
            'video' => 'nullable|mimetypes:video/mp4|max:25600',
            'poster_path' => 'nullable|string|max:255',
            'backdrop_path' => 'nullable|string|max:255',
            'poster_reset_tmdb' => 'nullable|boolean',
            'backdrop_reset_tmdb' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'genre_id.exists' => 'The selected genre does not exist.',
            'picture.max' => 'The image size should not exceed 50MB.',
            'video.max' => 'The demo clip must be 25MB or smaller.',

        ];
    }
}
