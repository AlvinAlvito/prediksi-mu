<?php

namespace App\Services;

use InvalidArgumentException;

class VideoLinkParser
{
    public function parse(string $link): string
    {
        $trimmed = trim($link);

        if ($trimmed === '') {
            throw new InvalidArgumentException('Link YouTube tidak boleh kosong.');
        }

        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $trimmed) === 1) {
            return $trimmed;
        }

        $parts = parse_url($trimmed);

        if (! is_array($parts)) {
            throw new InvalidArgumentException("Format link tidak valid: {$trimmed}");
        }

        if (($parts['host'] ?? '') === 'youtu.be') {
            $path = trim($parts['path'] ?? '', '/');

            if ($path !== '') {
                return $path;
            }
        }

        parse_str($parts['query'] ?? '', $query);

        if (! empty($query['v'])) {
            return $query['v'];
        }

        if (! empty($parts['path']) && preg_match('#/shorts/([A-Za-z0-9_-]{11})#', $parts['path'], $matches) === 1) {
            return $matches[1];
        }

        throw new InvalidArgumentException("Video ID tidak ditemukan dari link: {$trimmed}");
    }
}
