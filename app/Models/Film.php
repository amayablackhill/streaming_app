<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    use HasFactory;

    protected $fillable = [];

    /**
     * Relación polimórfica con Content
     */
    public function content()
    {
        return $this->morphOne(Content::class, 'contentable');
    }
}