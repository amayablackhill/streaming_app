<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id',
        'episode_number',
        'title',
        'duration',
        'release_date',
        'plot',
        'cover_path',
        'episode_path'
    ];

    /**
     * Relación con Season
     */
    public function season()
    {
        return $this->belongsTo(Season::class);
    }
}