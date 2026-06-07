<?php

namespace App\Services;

class NaiveBayesService
{
    public function train(array $documents): array
    {
        $labels = [];
        $documentCountByLabel = [];
        $featureWeightByLabel = [];
        $totalFeatureWeightByLabel = [];
        $vocabulary = [];
        $totalDocuments = count($documents);

        foreach ($documents as $document) {
            $label = $document['label'];
            $features = $document['features'];

            $labels[$label] = true;
            $documentCountByLabel[$label] = ($documentCountByLabel[$label] ?? 0) + 1;

            foreach ($features as $token => $weight) {
                $vocabulary[$token] = true;
                $featureWeightByLabel[$label][$token] = ($featureWeightByLabel[$label][$token] ?? 0.0) + $weight;
                $totalFeatureWeightByLabel[$label] = ($totalFeatureWeightByLabel[$label] ?? 0.0) + $weight;
            }
        }

        return [
            'labels' => array_keys($labels),
            'document_count_by_label' => $documentCountByLabel,
            'feature_weight_by_label' => $featureWeightByLabel,
            'total_feature_weight_by_label' => $totalFeatureWeightByLabel,
            'vocabulary_size' => count($vocabulary),
            'total_documents' => $totalDocuments,
        ];
    }

    public function predict(array $model, array $features): array
    {
        $scores = [];

        foreach ($model['labels'] as $label) {
            $priorCount = $model['document_count_by_label'][$label] ?? 0;
            $prior = $priorCount > 0 && $model['total_documents'] > 0
                ? log($priorCount / $model['total_documents'])
                : log(1e-9);

            $score = $prior;
            $totalWeights = $model['total_feature_weight_by_label'][$label] ?? 0.0;
            $vocabularySize = max(1, $model['vocabulary_size']);

            foreach ($features as $token => $weight) {
                $featureWeight = $model['feature_weight_by_label'][$label][$token] ?? 0.0;
                $likelihood = ($featureWeight + 1) / ($totalWeights + $vocabularySize);
                $score += $weight * log($likelihood);
            }

            $scores[$label] = $score;
        }

        arsort($scores);

        return [
            'label' => array_key_first($scores) ?? 'netral',
            'scores' => $scores,
        ];
    }
}
