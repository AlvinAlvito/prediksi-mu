<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalysisRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'source_links',
        'video_ids',
        'video_metadata',
        'status',
        'total_comments',
        'analyzed_comments',
        'positive_count',
        'negative_count',
        'neutral_count',
        'accuracy',
        'precision',
        'recall',
        'f1_score',
        'evaluation',
    ];

    protected function casts(): array
    {
        return [
            'source_links' => 'array',
            'video_ids' => 'array',
            'video_metadata' => 'array',
            'evaluation' => 'array',
            'accuracy' => 'float',
            'precision' => 'float',
            'recall' => 'float',
            'f1_score' => 'float',
        ];
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
