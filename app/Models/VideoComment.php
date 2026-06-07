<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_id',
        'youtube_comment_id',
        'youtube_parent_id',
        'author_name',
        'original_text',
        'published_at',
        'like_count',
        'reply_count',
        'is_reply',
        'language_code',
        'is_indonesian',
        'language_score',
        'processed_text',
        'tokens',
        'manual_sentiment',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_reply' => 'boolean',
            'is_indonesian' => 'boolean',
            'language_score' => 'float',
            'tokens' => 'array',
            'raw_payload' => 'array',
        ];
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
