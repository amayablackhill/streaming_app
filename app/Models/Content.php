<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'genre_id',
        'release_date',
        'duration',
        'rating',
        'description',
        'director',
        'type',
        'picture',
        'video',
        'is_featured',
        'tmdb_id',
        'tmdb_type',
        'overview',
        'runtime_minutes',
        'rating_average',
        'rating_count',
        'poster_path',
        'backdrop_path',
        'youtube_trailer_id',
        'tmdb_last_synced_at',
    ];

    protected $casts = [
        'release_date' => 'date',
        'tmdb_last_synced_at' => 'datetime',
        'rating_average' => 'decimal:2',
        'is_featured' => 'boolean',
    ];

    public function genre()
    {
        return $this->belongsTo(Genre::class);
    }

    public function seasons()
    {
        return $this->hasMany(Season::class, 'serie_id');
    }

    public function videoAssets(): HasMany
    {
        return $this->hasMany(VideoAsset::class);
    }

    public function getPosterUrlAttribute(): ?string
    {
        $alternatePoster = $this->resolveAlternateImage($this->poster_path, 'w500');
        if ($alternatePoster) {
            return $alternatePoster;
        }

        return $this->legacyImageUrl();
    }

    public function getBackdropUrlAttribute(): ?string
    {
        $alternateBackdrop = $this->resolveAlternateImage($this->backdrop_path, 'original');
        if ($alternateBackdrop) {
            return $alternateBackdrop;
        }

        return $this->legacyImageUrl();
    }

    public function getDisplayOverviewAttribute(): ?string
    {
        return $this->overview ?: $this->description;
    }

    public function getDisplayRuntimeAttribute(): ?int
    {
        return $this->runtime_minutes ?: $this->duration;
    }

    private function tmdbImageUrl(string $path, string $size): string
    {
        $cleanPath = Str::start($path, '/');

        return "https://image.tmdb.org/t/p/{$size}{$cleanPath}";
    }

    private function resolveAlternateImage(?string $value, string $tmdbSize): ?string
    {
        $cleanValue = trim((string) $value);
        if ($cleanValue === '') {
            return null;
        }

        if (Str::startsWith($cleanValue, ['http://', 'https://'])) {
            return $cleanValue;
        }

        return $this->tmdbImageUrl($cleanValue, $tmdbSize);
    }

    private function legacyImageUrl(): ?string
    {
        if (empty($this->picture)) {
            return null;
        }

        if (Str::startsWith($this->picture, ['http://', 'https://'])) {
            return $this->picture;
        }

        $folder = $this->type === 'serie' ? 'series' : 'movies';

        return asset("storage/{$folder}/{$this->picture}");
    }
}
