<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id',
        'tmdb_id',
        'episode_number',
        'title',
        'duration',
        'runtime_minutes',
        'release_date',
        'plot',
        'cover_path',
        'still_path',
        'tmdb_last_synced_at',
        'episode_path',
    ];

    protected $casts = [
        'release_date' => 'date',
        'tmdb_last_synced_at' => 'datetime',
    ];

    /**
     * Relación con Season
     */
    public function season()
    {
        return $this->belongsTo(Season::class);
    }
}
