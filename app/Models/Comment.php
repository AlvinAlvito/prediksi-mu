<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'analysis_run_id',
        'video_id',
        'video_title',
        'author_name',
        'published_at',
        'original_text',
        'actual_sentiment',
        'processed_text',
        'tokens',
        'predicted_sentiment',
        'scores',
        'video_record_id',
        'youtube_comment_id',
        'comment_url',
        'like_count',
        'reply_count',
        'is_processed',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'tokens' => 'array',
            'scores' => 'array',
            'is_processed' => 'boolean',
            'raw_payload' => 'array',
        ];
    }

    public function analysisRun(): BelongsTo
    {
        return $this->belongsTo(AnalysisRun::class);
    }
}
