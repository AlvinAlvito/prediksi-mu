<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$counts = App\Models\VideoComment::query()
 ->where('is_indonesian', true)
 ->whereNotNull('manual_sentiment')
 ->selectRaw('manual_sentiment, count(*) as total')
 ->groupBy('manual_sentiment')
 ->pluck('total','manual_sentiment');
echo json_encode($counts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
