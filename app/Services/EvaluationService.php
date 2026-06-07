<?php

namespace App\Services;

class EvaluationService
{
    public function splitDocuments(array $documents, float $testRatio = 0.2): array
    {
        $grouped = [];

        foreach ($documents as $document) {
            $grouped[$document['label']][] = $document;
        }

        $train = [];
        $test = [];

        foreach ($grouped as $items) {
            $count = count($items);
            $testCount = max(1, (int) round($count * $testRatio));
            $testCount = min($testCount, max(1, $count - 1));

            $testItems = array_slice($items, 0, $testCount);
            $trainItems = array_slice($items, $testCount);

            if ($trainItems === []) {
                $trainItems = $testItems;
                $testItems = [];
            }

            $train = [...$train, ...$trainItems];
            $test = [...$test, ...$testItems];
        }

        return [$train, $test];
    }

    public function evaluate(array $labels, array $actualPredictedPairs): array
    {
        $matrix = [];

        foreach ($labels as $actual) {
            foreach ($labels as $predicted) {
                $matrix[$actual][$predicted] = 0;
            }
        }

        foreach ($actualPredictedPairs as $pair) {
            $matrix[$pair['actual']][$pair['predicted']]++;
        }

        $total = count($actualPredictedPairs);
        $correct = 0;
        $metricsByLabel = [];

        foreach ($labels as $label) {
            $tp = $matrix[$label][$label];
            $correct += $tp;

            $fp = 0;
            $fn = 0;
            $tn = 0;

            foreach ($labels as $rowLabel) {
                foreach ($labels as $colLabel) {
                    $value = $matrix[$rowLabel][$colLabel];

                    if ($rowLabel === $label && $colLabel !== $label) {
                        $fn += $value;
                    } elseif ($rowLabel !== $label && $colLabel === $label) {
                        $fp += $value;
                    } elseif ($rowLabel !== $label && $colLabel !== $label) {
                        $tn += $value;
                    }
                }
            }

            $precision = ($tp + $fp) > 0 ? $tp / ($tp + $fp) : 0.0;
            $recall = ($tp + $fn) > 0 ? $tp / ($tp + $fn) : 0.0;
            $f1 = ($precision + $recall) > 0 ? (2 * $precision * $recall) / ($precision + $recall) : 0.0;

            $metricsByLabel[$label] = [
                'precision' => $precision,
                'recall' => $recall,
                'f1_score' => $f1,
                'support' => array_sum($matrix[$label]),
                'tp' => $tp,
                'fp' => $fp,
                'fn' => $fn,
                'tn' => $tn,
            ];
        }

        $labelCount = max(1, count($labels));

        return [
            'confusion_matrix' => $matrix,
            'accuracy' => $total > 0 ? $correct / $total : 0.0,
            'precision' => array_sum(array_column($metricsByLabel, 'precision')) / $labelCount,
            'recall' => array_sum(array_column($metricsByLabel, 'recall')) / $labelCount,
            'f1_score' => array_sum(array_column($metricsByLabel, 'f1_score')) / $labelCount,
            'per_label' => $metricsByLabel,
        ];
    }
}
