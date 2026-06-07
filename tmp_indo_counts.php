<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$counts = App\Models\VideoComment::query()
 ->selectRaw('is_indonesian, count(*) as total')
 ->groupBy('is_indonesian')
 ->pluck('total','is_indonesian');
echo json_encode($counts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
