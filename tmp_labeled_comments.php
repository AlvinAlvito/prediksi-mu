<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$comments = App\Models\VideoComment::query()
  ->where('is_indonesian', true)
  ->whereNotNull('manual_sentiment')
  ->select('id','video_id','youtube_comment_id','original_text','processed_text','tokens','manual_sentiment')
  ->orderBy('id')
  ->limit(12)
  ->get();

echo json_encode($comments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
