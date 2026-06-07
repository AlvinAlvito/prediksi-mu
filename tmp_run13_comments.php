<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$comments = App\Models\Comment::query()
 ->where('analysis_run_id', 13)
 ->select('video_id','original_text','processed_text','actual_sentiment','predicted_sentiment')
 ->orderBy('id')
 ->limit(8)
 ->get();
echo json_encode($comments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
