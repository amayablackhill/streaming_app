<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_seasons',
    ];

    /**
     * Relación polimórfica con Content
     */
    public function content()
    {
        return $this->morphOne(Content::class, 'contentable');
    }

    /**
     * Relación con Seasons
     */
    public function seasons()
    {
        return $this->hasMany(Season::class, 'serie_id');
    }
}