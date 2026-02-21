<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'serie_id',
        'season_number',
        'release_date',
        'poster_path',
        'overview'
    ];

    /**
     * Relación con Series
     */
    public function contents()
    {
        return $this->belongsTo(Content::class, 'id');
    }

    /**
     * Relación con Episodes
     */
    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }
}
