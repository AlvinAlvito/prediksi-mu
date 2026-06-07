<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'youtube_video_id',
        'url',
        'title',
        'channel_title',
        'published_at',
        'youtube_comment_count',
        'youtube_view_count',
        'cached_comment_count',
        'last_comments_synced_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'last_comments_synced_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function cachedComments(): HasMany
    {
        return $this->hasMany(VideoComment::class);
    }
}
