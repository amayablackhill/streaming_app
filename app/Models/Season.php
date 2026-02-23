<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'serie_id',
        'tmdb_id',
        'season_number',
        'episode_count',
        'release_date',
        'poster_path',
        'overview',
        'tmdb_last_synced_at',
    ];

    protected $casts = [
        'release_date' => 'date',
        'tmdb_last_synced_at' => 'datetime',
    ];

    /**
     * Relación con Series
     */
    public function contents()
    {
        return $this->belongsTo(Content::class, 'serie_id');
    }

    public function content()
    {
        return $this->belongsTo(Content::class, 'serie_id');
    }

    /**
     * Relación con Episodes
     */
    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }
}
