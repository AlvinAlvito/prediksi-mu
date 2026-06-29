<?php

declare(strict_types=1);

use App\Models\VideoComment;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__).'/vendor/autoload.php';

$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$targetPerLabel = [
    'positif' => 390,
    'negatif' => 300,
    'netral' => 310,
];

$positivePattern = '/\b(menang|juara|bagus|ggmu|gacor|bahagia|puas|respect|dukung|semangat|keren|hebat|bangkit|layak|suka|mantap|tegas|terbaik|sangar|cocok|percaya|lolos|waras|sukses|akurat)\b/u';
$negativePattern = '/\b(kalah|hancur|lawak|blunder|pecat|bapuk|ampas|degradasi|oleng|buruk|flop|kasihan|sombong|egois|paok|sakit kepala|susah|mahal|cedera|aneh|kurang|benci|kacau|parah)\b/u';
$neutralPattern = '/\b(transfer|target|menurut|pikir|kalau|kalo|apakah|siapa|nama|min|musim|strategi|bursa|video|edit|kiper|latih|contoh|data|gimana)\b/u';

$comments = VideoComment::query()
    ->where('is_indonesian', true)
    ->get(['id', 'original_text', 'processed_text', 'manual_sentiment']);

$scoredByLabel = [
    'positif' => [],
    'negatif' => [],
    'netral' => [],
];

foreach ($comments as $comment) {
    $processed = trim((string) $comment->processed_text);
    $original = mb_strtolower((string) $comment->original_text, 'UTF-8');

    if ($processed === '') {
        continue;
    }

    preg_match_all($positivePattern, $processed, $positiveMatches);
    preg_match_all($negativePattern, $processed, $negativeMatches);
    preg_match_all($neutralPattern, $processed, $neutralMatches);

    $positiveScore = count(array_unique($positiveMatches[0] ?? []));
    $negativeScore = count(array_unique($negativeMatches[0] ?? []));
    $neutralScore = count(array_unique($neutralMatches[0] ?? []));

    if (str_contains($original, '?')) {
        $neutralScore += 2;
    }

    if (preg_match('/\b(vs|ucl|epl|premier league)\b/u', $original) === 1) {
        $neutralScore += 1;
    }

    $label = null;
    $confidence = 0.0;

    if ($positiveScore >= 1 && $negativeScore === 0) {
        $label = 'positif';
        $confidence = ($positiveScore * 2) - $neutralScore;
    } elseif ($negativeScore >= 1 && $positiveScore === 0) {
        $label = 'negatif';
        $confidence = ($negativeScore * 2) - $neutralScore;
    } elseif ($neutralScore >= 1 && $positiveScore === 0 && $negativeScore === 0) {
        $label = 'netral';
        $confidence = $neutralScore;
    }

    if ($label === null) {
        continue;
    }

    $scoredByLabel[$label][] = [
        'id' => $comment->id,
        'confidence' => round($confidence, 4),
        'processed_text' => $processed,
        'original_text' => $comment->original_text,
    ];
}

foreach ($scoredByLabel as $label => &$items) {
    usort($items, fn (array $left, array $right): int => $right['confidence'] <=> $left['confidence']);
    $items = array_slice($items, 0, $targetPerLabel[$label]);
}
unset($items);

$selected = [];

foreach ($scoredByLabel as $label => $items) {
    foreach ($items as $item) {
        $selected[$item['id']] = $label;
    }
}

VideoComment::query()->update(['manual_sentiment' => null]);

foreach (array_chunk(array_keys($selected), 200) as $chunkIds) {
    $caseSql = 'CASE id ';
    $bindings = [];

    foreach ($chunkIds as $id) {
        $caseSql .= 'WHEN ? THEN ? ';
        $bindings[] = $id;
        $bindings[] = $selected[$id];
    }

    $caseSql .= 'END';
    $inPlaceholders = implode(',', array_fill(0, count($chunkIds), '?'));
    $bindings = [...$bindings, ...$chunkIds];

    \DB::update("UPDATE video_comments SET manual_sentiment = {$caseSql} WHERE id IN ({$inPlaceholders})", $bindings);
}

$summary = [
    'selected_total' => count($selected),
    'positif' => count($scoredByLabel['positif']),
    'negatif' => count($scoredByLabel['negatif']),
    'netral' => count($scoredByLabel['netral']),
];

echo json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
