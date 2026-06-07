<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$run = App\Models\AnalysisRun::find(13);
if (! $run) { echo "RUN_NOT_FOUND\n"; exit(1); }
echo json_encode([
 'id' => $run->id,
 'total_comments' => $run->total_comments,
 'analyzed_comments' => $run->analyzed_comments,
 'positive_count' => $run->positive_count,
 'negative_count' => $run->negative_count,
 'neutral_count' => $run->neutral_count,
 'accuracy' => $run->accuracy,
 'precision' => $run->precision,
 'recall' => $run->recall,
 'f1_score' => $run->f1_score,
 'training_sample_count' => $run->training_sample_count,
 'testing_sample_count' => $run->testing_sample_count,
 'evaluation' => $run->evaluation,
 'video_metadata' => $run->video_metadata,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
