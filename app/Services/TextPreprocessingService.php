<?php

namespace App\Services;

use Sastrawi\Stemmer\StemmerFactory;

class TextPreprocessingService
{
    private array $stopwords = [
        'ada', 'adalah', 'agar', 'aja', 'akan', 'aku', 'anda', 'atau', 'banget', 'baru', 'bagi',
        'bahwa', 'banyak', 'beberapa', 'begitu', 'belum', 'bisa', 'buat', 'dalam', 'dan', 'dari',
        'dengan', 'dia', 'di', 'dong', 'ga', 'gak', 'gue', 'harus', 'hanya', 'ini', 'itu', 'jadi',
        'jangan', 'juga', 'kalau', 'kami', 'kamu', 'kan', 'karena', 'ke', 'kemarin', 'kok', 'lagi',
        'lah', 'lebih', 'lo', 'lu', 'masih', 'mau', 'mereka', 'nya', 'nggak', 'nih', 'pada', 'para',
        'pun', 'saja', 'sama', 'sangat', 'saya', 'sebagai', 'sehingga', 'seperti', 'sih', 'sudah',
        'supaya', 'tak', 'tanpa', 'tapi', 'telah', 'tentang', 'terlalu', 'tidak', 'toh', 'untuk',
        'ya', 'yang', 'yg',
    ];

    private array $normalizationMap = [
        'bgt' => 'banget',
        'bgtt' => 'banget',
        'dr' => 'dari',
        'ga' => 'tidak',
        'gak' => 'tidak',
        'gpp' => 'tidak apa apa',
        'jd' => 'jadi',
        'jg' => 'juga',
        'krn' => 'karena',
        'mantul' => 'mantap',
        'nggak' => 'tidak',
        'tdk' => 'tidak',
        'udh' => 'sudah',
        'udah' => 'sudah',
        'utd' => 'united',
        'yg' => 'yang',
    ];

    public function __construct(
        private readonly StemmerFactory $stemmerFactory = new StemmerFactory(),
    ) {
    }

    public function process(string $text): array
    {
        $cleaned = $this->clean($text);
        $caseFolded = mb_strtolower($cleaned, 'UTF-8');
        $normalized = strtr($caseFolded, $this->normalizationMap);
        $tokens = $this->tokenize($normalized);
        $filteredTokens = array_values(array_filter($tokens, fn (string $token): bool => ! in_array($token, $this->stopwords, true)));
        $stemmedText = $this->stemmerFactory->createStemmer()->stem(implode(' ', $filteredTokens));
        $stemmedTokens = array_values(array_filter($this->tokenize($stemmedText)));

        return [
            'cleaned' => $cleaned,
            'processed_text' => implode(' ', $stemmedTokens),
            'tokens' => $stemmedTokens,
        ];
    }

    private function clean(string $text): string
    {
        $text = preg_replace('/https?:\/\/\S+/i', ' ', $text) ?? $text;
        $text = preg_replace('/@\w+/u', ' ', $text) ?? $text;
        $text = preg_replace('/[#&]/u', ' ', $text) ?? $text;
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private function tokenize(string $text): array
    {
        if ($text === '') {
            return [];
        }

        return array_values(array_filter(
            preg_split('/\s+/u', trim($text)) ?: [],
            fn (string $token): bool => $token !== ''
        ));
    }
}
