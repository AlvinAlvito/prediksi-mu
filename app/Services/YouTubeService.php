<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class YouTubeService
{
    private const BASE_URL = 'https://www.googleapis.com/youtube/v3';

    public function fetchVideoBundle(string $videoId): array
    {
        return [
            'video' => $this->fetchVideoMetadata($videoId),
            'comments' => $this->fetchComments($videoId),
        ];
    }

    public function fetchVideoMetadata(string $videoId): array
    {
        $response = Http::timeout($this->requestTimeout())
            ->retry(3, 1000)
            ->get(self::BASE_URL.'/videos', [
                'part' => 'snippet,statistics',
                'id' => $videoId,
                'key' => $this->apiKey(),
            ])
            ->throw()
            ->json();

        $item = $response['items'][0] ?? null;

        if ($item === null) {
            throw new RuntimeException("Metadata video tidak ditemukan untuk ID {$videoId}.");
        }

        return [
            'video_id' => $videoId,
            'title' => $item['snippet']['title'] ?? $videoId,
            'channel_title' => $item['snippet']['channelTitle'] ?? '-',
            'published_at' => $item['snippet']['publishedAt'] ?? null,
            'comment_count' => (int) ($item['statistics']['commentCount'] ?? 0),
            'view_count' => (int) ($item['statistics']['viewCount'] ?? 0),
        ];
    }

    public function fetchComments(string $videoId): array
    {
        $comments = [];
        $pageToken = null;
        $maxResults = (int) config('sentiment.max_comments_per_video', 0);
        $isUnlimited = $maxResults <= 0;

        do {
            $remaining = $isUnlimited ? 100 : max(0, $maxResults - count($comments));

            if (! $isUnlimited && $remaining === 0) {
                break;
            }

            $response = Http::timeout($this->requestTimeout())
                ->retry(3, 1000)
                ->get(self::BASE_URL.'/commentThreads', array_filter([
                    'part' => 'id,snippet,replies',
                    'videoId' => $videoId,
                    'maxResults' => min(100, $remaining),
                    'order' => 'relevance',
                    'textFormat' => 'plainText',
                    'pageToken' => $pageToken,
                    'key' => $this->apiKey(),
                ]))
                ->throw()
                ->json();

            foreach ($response['items'] ?? [] as $item) {
                $topLevelComment = $item['snippet']['topLevelComment'] ?? null;
                $threadReplyCount = (int) ($item['snippet']['totalReplyCount'] ?? 0);

                if (is_array($topLevelComment)) {
                    $comments[] = $this->mapComment($videoId, $topLevelComment, false);
                }

                $embeddedReplies = $item['replies']['comments'] ?? [];

                foreach ($embeddedReplies as $reply) {
                    $comments[] = $this->mapComment($videoId, $reply, true);
                }

                if ($threadReplyCount > count($embeddedReplies) && isset($topLevelComment['id'])) {
                    $comments = [
                        ...$comments,
                        ...$this->fetchReplies($videoId, $topLevelComment['id']),
                    ];
                }
            }

            $pageToken = $response['nextPageToken'] ?? null;
        } while ($pageToken !== null && ($isUnlimited || count($comments) < $maxResults));

        return $comments;
    }

    private function fetchReplies(string $videoId, string $parentCommentId): array
    {
        $replies = [];
        $pageToken = null;

        do {
            $response = Http::timeout($this->requestTimeout())
                ->retry(3, 1000)
                ->get(self::BASE_URL.'/comments', array_filter([
                    'part' => 'id,snippet',
                    'parentId' => $parentCommentId,
                    'maxResults' => 100,
                    'textFormat' => 'plainText',
                    'pageToken' => $pageToken,
                    'key' => $this->apiKey(),
                ]))
                ->throw()
                ->json();

            foreach ($response['items'] ?? [] as $item) {
                $replies[] = $this->mapComment($videoId, $item, true);
            }

            $pageToken = $response['nextPageToken'] ?? null;
        } while ($pageToken !== null);

        return $replies;
    }

    private function mapComment(string $videoId, array $comment, bool $isReply): array
    {
        $snippet = $comment['snippet'] ?? [];

        return [
            'video_id' => $videoId,
            'youtube_comment_id' => $comment['id'] ?? Str::uuid()->toString(),
            'youtube_parent_id' => $snippet['parentId'] ?? null,
            'author_name' => $snippet['authorDisplayName'] ?? 'Anonim',
            'text' => $snippet['textDisplay'] ?? '',
            'published_at' => $snippet['publishedAt'] ?? null,
            'like_count' => (int) ($snippet['likeCount'] ?? 0),
            'reply_count' => (int) ($snippet['totalReplyCount'] ?? 0),
            'is_reply' => $isReply,
            'raw_payload' => $comment,
        ];
    }

    private function apiKey(): string
    {
        $key = config('services.youtube.key');

        if (! is_string($key) || trim($key) === '') {
            throw new RuntimeException('YOUTUBE_API_KEY belum diisi pada file .env.');
        }

        return $key;
    }

    private function requestTimeout(): int
    {
        return max(15, (int) config('sentiment.youtube_request_timeout', 60));
    }
}
