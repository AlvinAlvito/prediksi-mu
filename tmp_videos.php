<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$videos = App\Models\Video::query()->select('id','youtube_video_id','title','channel_title','public_comment_count','cached_comment_count','last_comments_synced_at')->orderBy('id')->get();
echo json_encode($videos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
