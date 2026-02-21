<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoAsset extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY = 'ready';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'uuid',
        'content_id',
        'original_filename',
        'source_disk',
        'source_path',
        'hls_disk',
        'hls_master_path',
        'thumbnails_path',
        'status',
        'duration_seconds',
        'width',
        'height',
        'video_bitrate',
        'meta',
        'error_message',
        'processed_at',
        'failed_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
        'duration_seconds' => 'decimal:2',
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
