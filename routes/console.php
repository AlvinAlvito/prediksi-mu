<?php

use App\Models\VideoComment;
use App\Services\IndonesianLanguageService;
use App\Services\TextPreprocessingService;
use App\Services\SentimentAnalysisService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('youtube:analyze {links?*} {--default} {--refresh} {--cache-only}', function (SentimentAnalysisService $sentimentAnalysisService) {
    try {
        $links = $this->option('default')
            ? config('sentiment.default_links', [])
            : ($this->argument('links') ?: []);

        if ($links === []) {
            $this->error('Masukkan minimal satu link YouTube atau gunakan opsi --default.');

            return self::FAILURE;
        }

        $run = $sentimentAnalysisService->analyzeLinks(
            $links,
            $this->option('default') ? 'console-default' : 'console-manual',
            (bool) $this->option('refresh'),
            (bool) $this->option('cache-only')
        );

        $this->info("Analisis selesai. Run ID: {$run->id}");
        $this->line("Komentar dianalisis: {$run->analyzed_comments}");
        $this->line("Positif: {$run->positive_count}");
        $this->line("Negatif: {$run->negative_count}");
        $this->line("Netral: {$run->neutral_count}");
        $this->line('Accuracy: '.number_format(($run->accuracy ?? 0) * 100, 2).'%');
        $this->line('F1-score: '.number_format(($run->f1_score ?? 0) * 100, 2).'%');
        $this->line("Buka hasil di /runs/{$run->id} setelah server Laravel dijalankan.");

        return self::SUCCESS;
    } catch (\Throwable $throwable) {
        $this->error($throwable->getMessage());

        return self::FAILURE;
    }
})->purpose('Ambil komentar YouTube dan jalankan analisis sentimen');

Artisan::command('dataset:backfill-cache', function (
    TextPreprocessingService $textPreprocessingService,
    IndonesianLanguageService $indonesianLanguageService
) {
    $updated = 0;

    VideoComment::query()
        ->orderBy('id')
        ->chunkById(200, function ($comments) use ($textPreprocessingService, $indonesianLanguageService, &$updated) {
            foreach ($comments as $comment) {
                $processed = $textPreprocessingService->process($comment->original_text);
                $language = $indonesianLanguageService->detect($comment->original_text);

                $comment->update([
                    'language_code' => $language['language_code'],
                    'is_indonesian' => $language['is_indonesian'],
                    'language_score' => $language['score'],
                    'processed_text' => $processed['processed_text'],
                    'tokens' => $processed['tokens'],
                ]);

                $updated++;
            }
        });

    $this->info("Backfill selesai. {$updated} komentar cache diperbarui.");

    return self::SUCCESS;
})->purpose('Isi preprocessing dan deteksi bahasa untuk komentar cache yang sudah ada');
