<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SeasonRequest extends FormRequest
{
    public function authorize()
    {
        return true;
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
            'type' => 'nullable|string',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg|max:51200',
            'video' => 'nullable|mimetypes:video/mp4',
        ];
    }

    public function messages()
    {
        return [
            'genre_id.exists' => 'The selected genre does not exist.',
            'picture.max' => 'The image size should not exceed 50MB.',

        ];
    }
}