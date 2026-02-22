<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
