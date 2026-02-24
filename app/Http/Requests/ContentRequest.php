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
            'picture' => $this->imageUploadRules(),
            'poster_image' => $this->imageUploadRules(),
            'backdrop_image' => $this->imageUploadRules(),
            'video' => ['nullable', 'file', 'mimes:mp4', 'mimetypes:video/mp4', 'max:25600'],
            'poster_path' => $this->artworkPathRules(),
            'backdrop_path' => $this->artworkPathRules(),
            'poster_reset_tmdb' => 'nullable|boolean',
            'backdrop_reset_tmdb' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'genre_id.exists' => 'The selected genre does not exist.',
            'picture.max' => 'The image size should not exceed 10MB.',
            'poster_image.max' => 'The image size should not exceed 10MB.',
            'backdrop_image.max' => 'The image size should not exceed 10MB.',
            'video.max' => 'The demo clip must be 25MB or smaller.',
            'video.mimes' => 'Only MP4 demo clips are allowed.',
            'poster_path.max' => 'Artwork URL/path is too long.',
            'backdrop_path.max' => 'Artwork URL/path is too long.',

        ];
    }

    private function imageUploadRules(): array
    {
        return [
            'nullable',
            'file',
            'image',
            'mimetypes:image/jpeg,image/png,image/webp',
            'max:10240',
            'dimensions:max_width=6000,max_height=6000',
        ];
    }

    private function artworkPathRules(): array
    {
        return [
            'nullable',
            'string',
            'max:2048',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === null || trim((string) $value) === '') {
                    return;
                }

                $normalized = trim((string) $value);

                $isHttpUrl = filter_var($normalized, FILTER_VALIDATE_URL)
                    && preg_match('/^https?:\/\//i', $normalized) === 1;
                $isTmdbLikePath = preg_match('#^/[A-Za-z0-9._\-/]+$#', $normalized) === 1;

                if (! $isHttpUrl && ! $isTmdbLikePath) {
                    $fail('Use a valid TMDB path (/image.jpg) or a full http/https URL.');
                }
            },
        ];
    }
}
