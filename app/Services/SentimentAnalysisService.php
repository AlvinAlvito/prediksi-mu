<?php

namespace App\Services;

use App\Models\AnalysisRun;
use App\Models\Comment;
use App\Models\Video;
use App\Models\VideoComment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SentimentAnalysisService
{
    private array $labels = ['positif', 'negatif', 'netral'];

    public function __construct(
        private readonly VideoLinkParser $videoLinkParser,
        private readonly YouTubeService $youTubeService,
        private readonly TextPreprocessingService $textPreprocessingService,
        private readonly IndonesianLanguageService $indonesianLanguageService,
        private readonly TfIdfService $tfIdfService,
        private readonly NaiveBayesService $naiveBayesService,
        private readonly EvaluationService $evaluationService,
    ) {
    }

    public function analyzeLinks(
        array $links,
        string $source = 'web',
        bool $forceRefresh = false,
        bool $cacheOnly = false
    ): AnalysisRun
    {
        $this->configureExecutionTime();

        $resolvedLinks = array_values(array_filter(array_map('trim', $links)));
        $videoIds = array_values(array_unique(array_map(
            fn (string $link): string => $this->videoLinkParser->parse($link),
            $resolvedLinks
        )));
        $resolvedDatasets = [];

        foreach ($resolvedLinks as $link) {
            $videoId = $this->videoLinkParser->parse($link);
            $resolvedDatasets[$videoId] = $this->resolveVideoDataset($videoId, $link, $forceRefresh, $cacheOnly);
        }

        $trainingDocuments = $this->buildTrainingDocuments($videoIds);
        $isDatasetSyncOnly = $forceRefresh && count($trainingDocuments) < 9;

        if ($isDatasetSyncOnly) {
            return AnalysisRun::create([
                'source' => $source,
                'source_links' => $resolvedLinks,
                'video_ids' => array_keys($resolvedDatasets),
                'status' => 'completed',
                'algorithm' => 'naive_bayes',
                'feature_extractor' => 'tf_idf',
                'training_sample_count' => 0,
                'testing_sample_count' => 0,
                'run_config' => [
                    'reuse_cached_dataset' => (bool) config('sentiment.reuse_cached_dataset', true),
                    'max_comments_per_video' => (int) config('sentiment.max_comments_per_video', 0),
                    'force_refresh' => $forceRefresh,
                    'cache_only' => $cacheOnly,
                    'sync_only' => true,
                ],
                'started_at' => now(),
                'finished_at' => now(),
                'video_metadata' => array_values(array_map(
                    fn (array $dataset): array => $dataset['metadata'],
                    $resolvedDatasets
                )),
                'total_comments' => array_sum(array_map(
                    fn (array $dataset): int => $dataset['comments']->count(),
                    $resolvedDatasets
                )),
                'analyzed_comments' => 0,
                'positive_count' => 0,
                'negative_count' => 0,
                'neutral_count' => 0,
                'evaluation' => [
                    'confusion_matrix' => [],
                    'per_label' => [],
                    'sync_only' => true,
                    'message' => 'Dataset cache berhasil diperbarui. Lakukan pelabelan manual sebelum analisis final.',
                ],
            ]);
        }

        if (count($trainingDocuments) < 9) {
            throw new RuntimeException(
                'Label manual komentar YouTube masih terlalu sedikit. Buka menu "Label dataset cache" dan labelkan komentar Bahasa Indonesia terlebih dahulu.'
            );
        }

        [$trainDocuments, $testDocuments] = $this->evaluationService->splitDocuments(
            $trainingDocuments,
            (float) config('sentiment.evaluation_test_ratio', 0.2)
        );
        $trainingSampleCount = count($trainDocuments);
        $testingSampleCount = count($testDocuments);

        $tfIdfModel = $this->tfIdfService->fit($trainDocuments);
        $trainFeatureDocuments = array_map(function (array $document) use ($tfIdfModel): array {
            return [
                'label' => $document['label'],
                'features' => $this->tfIdfService->transformTokens($document['tokens'], $tfIdfModel),
            ];
        }, $trainDocuments);

        $evaluationModel = $this->naiveBayesService->train($trainFeatureDocuments);
        $pairs = [];

        foreach ($testDocuments as $document) {
            $features = $this->tfIdfService->transformTokens($document['tokens'], $tfIdfModel);
            $prediction = $this->naiveBayesService->predict($evaluationModel, $features);
            $pairs[] = [
                'actual' => $document['label'],
                'predicted' => $prediction['label'],
            ];
        }

        $evaluation = $this->evaluationService->evaluate($this->labels, $pairs);
        $allFeatureDocuments = array_map(function (array $document) use ($tfIdfModel): array {
            return [
                'label' => $document['label'],
                'features' => $this->tfIdfService->transformTokens($document['tokens'], $tfIdfModel),
            ];
        }, $trainingDocuments);
        $finalModel = $this->naiveBayesService->train($allFeatureDocuments);

        return DB::transaction(function () use (
            $source,
            $resolvedLinks,
            $resolvedDatasets,
            $evaluation,
            $tfIdfModel,
            $finalModel,
            $trainingSampleCount,
            $testingSampleCount,
            $forceRefresh,
            $cacheOnly
        ): AnalysisRun {
            $run = AnalysisRun::create([
                'source' => $source,
                'source_links' => $resolvedLinks,
                'video_ids' => array_keys($resolvedDatasets),
                'status' => 'processing',
                'algorithm' => 'naive_bayes',
                'feature_extractor' => 'tf_idf',
                'training_sample_count' => $trainingSampleCount,
                'testing_sample_count' => $testingSampleCount,
                'run_config' => [
                    'reuse_cached_dataset' => (bool) config('sentiment.reuse_cached_dataset', true),
                    'max_comments_per_video' => (int) config('sentiment.max_comments_per_video', 0),
                    'force_refresh' => $forceRefresh,
                    'cache_only' => $cacheOnly,
                ],
                'started_at' => now(),
                'evaluation' => $evaluation,
                'accuracy' => $evaluation['accuracy'],
                'precision' => $evaluation['precision'],
                'recall' => $evaluation['recall'],
                'f1_score' => $evaluation['f1_score'],
            ]);

            $videoMetadata = [];
            $counts = [
                'positif' => 0,
                'negatif' => 0,
                'netral' => 0,
            ];
            $totalComments = 0;
            $analyzedComments = 0;

            foreach ($resolvedDatasets as $videoId => $dataset) {
                $video = $dataset['video'];
                $videoMetadata[] = $dataset['metadata'];
                $videoComments = $dataset['comments'];

                foreach ($videoComments as $rawComment) {
                    $totalComments++;
                    if (! $rawComment->is_indonesian || $rawComment->processed_text === null) {
                        continue;
                    }

                    $features = $this->tfIdfService->transformTokens($rawComment->tokens ?? [], $tfIdfModel);

                    if ($features === []) {
                        continue;
                    }

                    $prediction = $this->naiveBayesService->predict($finalModel, $features);
                    $counts[$prediction['label']]++;
                    $analyzedComments++;

                    Comment::create([
                        'analysis_run_id' => $run->id,
                        'video_id' => $videoId,
                        'video_title' => $video->title,
                        'author_name' => $rawComment->author_name,
                        'published_at' => $rawComment->published_at,
                        'original_text' => $rawComment->original_text,
                        'actual_sentiment' => $rawComment->manual_sentiment,
                        'processed_text' => $rawComment->processed_text,
                        'tokens' => $rawComment->tokens,
                        'predicted_sentiment' => $prediction['label'],
                        'scores' => $prediction['scores'],
                        'video_record_id' => $video->id,
                        'youtube_comment_id' => $rawComment->youtube_comment_id,
                        'like_count' => $rawComment->like_count,
                        'reply_count' => $rawComment->reply_count,
                        'is_processed' => true,
                        'raw_payload' => $rawComment->raw_payload,
                    ]);
                }
            }

            $run->update([
                'status' => 'completed',
                'video_metadata' => $videoMetadata,
                'total_comments' => $totalComments,
                'analyzed_comments' => $analyzedComments,
                'positive_count' => $counts['positif'],
                'negative_count' => $counts['negatif'],
                'neutral_count' => $counts['netral'],
                'finished_at' => now(),
            ]);

            return $run->fresh(['comments']);
        });
    }

    public function analyzeDefaultLinks(
        string $source = 'console-default',
        bool $forceRefresh = false,
        bool $cacheOnly = false
    ): AnalysisRun
    {
        return $this->analyzeLinks(config('sentiment.default_links', []), $source, $forceRefresh, $cacheOnly);
    }

    private function buildTrainingDocuments(array $videoIds): array
    {
        return VideoComment::query()
            ->whereHas('video', fn ($query) => $query->whereIn('youtube_video_id', $videoIds))
            ->where('is_indonesian', true)
            ->whereNotNull('manual_sentiment')
            ->whereNotNull('processed_text')
            ->orderBy('manual_sentiment')
            ->orderBy('id')
            ->get()
            ->map(function (VideoComment $comment): array {
                return [
                    'label' => $comment->manual_sentiment,
                    'tokens' => $comment->tokens ?? [],
                ];
            })
            ->filter(fn (array $document): bool => $document['tokens'] !== [])
            ->values()
            ->all();
    }

    private function resolveVideoDataset(
        string $videoId,
        string $link,
        bool $forceRefresh = false,
        bool $cacheOnly = false
    ): array
    {
        $reuseCachedDataset = (bool) config('sentiment.reuse_cached_dataset', true);
        $video = Video::query()
            ->where('youtube_video_id', $videoId)
            ->with('cachedComments')
            ->first();

        if (! $forceRefresh && $video !== null && $reuseCachedDataset && $video->cachedComments->isNotEmpty()) {
            return [
                'video' => $video,
                'metadata' => [
                    'video_id' => $video->youtube_video_id,
                    'title' => $video->title,
                    'channel_title' => $video->channel_title,
                    'published_at' => $video->published_at?->toISOString(),
                    'comment_count' => $video->youtube_comment_count,
                    'view_count' => $video->youtube_view_count,
                    'cached_comment_count' => $video->cached_comment_count,
                    'last_comments_synced_at' => $video->last_comments_synced_at?->toISOString(),
                    'dataset_source' => 'database_cache',
                ],
                'comments' => $video->cachedComments,
            ];
        }

        if ($cacheOnly) {
            throw new RuntimeException(
                "Cache dataset untuk video {$videoId} belum tersedia. Jalankan sekali 'Refresh dari YouTube API' terlebih dahulu."
            );
        }

        $bundle = $this->youTubeService->fetchVideoBundle($videoId);
        $metadata = $bundle['video'];

        $video ??= new Video();
        $video->fill([
            'youtube_video_id' => $videoId,
            'url' => $link,
            'title' => $metadata['title'],
            'channel_title' => $metadata['channel_title'],
            'published_at' => $this->toDatabaseDateTime($metadata['published_at']),
            'youtube_comment_count' => $metadata['comment_count'],
            'youtube_view_count' => $metadata['view_count'],
            'metadata' => $metadata,
            'last_comments_synced_at' => now(),
        ]);
        $video->save();

        VideoComment::query()->where('video_id', $video->id)->delete();

        $rows = [];
        $seenCommentIds = [];

        foreach ($bundle['comments'] as $comment) {
            $youtubeCommentId = $comment['youtube_comment_id'];

            if (isset($seenCommentIds[$youtubeCommentId])) {
                continue;
            }

            $seenCommentIds[$youtubeCommentId] = true;
            $processed = $this->textPreprocessingService->process($comment['text']);
            $language = $this->indonesianLanguageService->detect($comment['text']);

            $rows[] = [
                'video_id' => $video->id,
                'youtube_comment_id' => $youtubeCommentId,
                'youtube_parent_id' => $comment['youtube_parent_id'],
                'author_name' => $comment['author_name'],
                'original_text' => $comment['text'],
                'published_at' => $this->toDatabaseDateTime($comment['published_at']),
                'like_count' => $comment['like_count'],
                'reply_count' => $comment['reply_count'],
                'is_reply' => $comment['is_reply'],
                'language_code' => $language['language_code'],
                'is_indonesian' => $language['is_indonesian'],
                'language_score' => $language['score'],
                'processed_text' => $processed['processed_text'],
                'tokens' => json_encode($processed['tokens'], JSON_UNESCAPED_UNICODE),
                'manual_sentiment' => null,
                'raw_payload' => json_encode($comment['raw_payload'], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('video_comments')->insert($chunk);
        }

        $video->update([
            'cached_comment_count' => count($rows),
        ]);

        return [
            'video' => $video->fresh('cachedComments'),
            'metadata' => [
                ...$metadata,
                'cached_comment_count' => count($rows),
                'last_comments_synced_at' => now()->toISOString(),
                'dataset_source' => 'youtube_api',
            ],
            'comments' => $video->fresh('cachedComments')->cachedComments,
        ];
    }

    private function toDatabaseDateTime(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    private function configureExecutionTime(): void
    {
        $seconds = (int) config('sentiment.max_execution_time', 0);

        if (function_exists('set_time_limit')) {
            @set_time_limit($seconds <= 0 ? 0 : $seconds);
        }

        @ini_set('max_execution_time', (string) ($seconds <= 0 ? 0 : $seconds));
    }
}
