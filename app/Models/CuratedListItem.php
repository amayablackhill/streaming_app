<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuratedListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'curated_list_id',
        'content_id',
        'rank',
    ];

    public function curatedList(): BelongsTo
    {
        return $this->belongsTo(CuratedList::class);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}

