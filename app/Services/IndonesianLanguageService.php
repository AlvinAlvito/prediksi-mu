<?php

namespace App\Services;

class IndonesianLanguageService
{
    private array $indicatorWords = [
        'ada', 'adalah', 'akan', 'aku', 'anda', 'apa', 'bagus', 'banget', 'bisa', 'buat', 'dalam',
        'dan', 'dari', 'dengan', 'dia', 'ga', 'gak', 'gue', 'ini', 'itu', 'jadi', 'juga', 'kalau',
        'karena', 'kita', 'kalah', 'komentar', 'lagi', 'main', 'mainnya', 'mau', 'menang', 'nggak',
        'pemain', 'pelatih', 'pertandingan', 'sangat', 'saya', 'semoga', 'sih', 'sudah', 'tidak',
        'tim', 'untuk', 'yang',
    ];

    public function detect(string $text): array
    {
        $normalized = mb_strtolower($text, 'UTF-8');
        $normalized = preg_replace('/[^\p{L}\s]/u', ' ', $normalized) ?? $normalized;
        $tokens = array_values(array_filter(preg_split('/\s+/u', trim($normalized)) ?: []));

        if ($tokens === []) {
            return [
                'language_code' => 'unknown',
                'is_indonesian' => false,
                'score' => 0.0,
            ];
        }

        $hits = 0;

        foreach ($tokens as $token) {
            if (in_array($token, $this->indicatorWords, true)) {
                $hits++;
            }
        }

        $score = $hits / max(1, count($tokens));
        $isIndonesian = $hits >= 2 || $score >= 0.2;

        return [
            'language_code' => $isIndonesian ? 'id' : 'unknown',
            'is_indonesian' => $isIndonesian,
            'score' => round($score, 4),
        ];
    }
}
