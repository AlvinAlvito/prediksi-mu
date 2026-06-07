<?php

namespace App\Services;

class TfIdfService
{
    public function fit(array $documents): array
    {
        $documentCount = count($documents);
        $documentFrequency = [];

        foreach ($documents as $document) {
            $uniqueTokens = array_values(array_unique($document['tokens']));

            foreach ($uniqueTokens as $token) {
                $documentFrequency[$token] = ($documentFrequency[$token] ?? 0) + 1;
            }
        }

        $idf = [];

        foreach ($documentFrequency as $token => $df) {
            $idf[$token] = log((1 + $documentCount) / (1 + $df)) + 1;
        }

        return [
            'document_count' => $documentCount,
            'idf' => $idf,
            'vocabulary' => array_keys($idf),
        ];
    }

    public function transformTokens(array $tokens, array $model): array
    {
        if ($tokens === []) {
            return [];
        }

        $termCounts = [];

        foreach ($tokens as $token) {
            $termCounts[$token] = ($termCounts[$token] ?? 0) + 1;
        }

        $totalTerms = array_sum($termCounts);
        $weights = [];

        foreach ($termCounts as $token => $count) {
            if (! isset($model['idf'][$token])) {
                continue;
            }

            $tf = $count / max(1, $totalTerms);
            $weights[$token] = $tf * $model['idf'][$token];
        }

        return $weights;
    }
}
