<?php

namespace App\Http\Controllers;

use App\Models\AnalysisRun;
use App\Services\SentimentAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AnalysisController extends Controller
{
    public function index(): View
    {
        return view('analysis.index', [
            'defaultLinks' => implode(PHP_EOL, config('sentiment.default_links', [])),
            'recentRuns' => AnalysisRun::query()->latest()->take(5)->get(),
        ]);
    }

    public function store(Request $request, SentimentAnalysisService $sentimentAnalysisService): RedirectResponse
    {
        $validated = $request->validate([
            'links' => ['nullable', 'string'],
            'mode' => ['required', 'in:manual,default,cache,refresh'],
        ]);

        $links = in_array($validated['mode'], ['default', 'cache', 'refresh'], true)
            ? config('sentiment.default_links', [])
            : (preg_split('/\r\n|\r|\n/', trim((string) ($validated['links'] ?? ''))) ?: []);
        $forceRefresh = $validated['mode'] === 'refresh';
        $cacheOnly = $validated['mode'] === 'cache';

        if ($links === []) {
            return back()->withErrors([
                'links' => 'Masukkan minimal satu link YouTube.',
            ])->withInput();
        }

        try {
            $run = $sentimentAnalysisService->analyzeLinks($links, 'web-ui', $forceRefresh, $cacheOnly);
        } catch (Throwable $throwable) {
            return back()->withErrors([
                'links' => $throwable->getMessage(),
            ])->withInput();
        }

        return redirect()->route('analysis.show', $run);
    }

    public function show(AnalysisRun $analysisRun): View
    {
        $analysisRun->load(['comments' => fn ($query) => $query->latest()->limit(50)]);

        $distribution = [
            'positif' => $analysisRun->positive_count,
            'negatif' => $analysisRun->negative_count,
            'netral' => $analysisRun->neutral_count,
        ];

        $total = max(1, array_sum($distribution));
        $percentages = [];

        foreach ($distribution as $label => $value) {
            $percentages[$label] = round(($value / $total) * 100, 2);
        }

        $datasetSources = collect($analysisRun->video_metadata ?? [])
            ->pluck('dataset_source')
            ->filter()
            ->unique()
            ->values();

        return view('analysis.show', [
            'run' => $analysisRun,
            'distribution' => $distribution,
            'percentages' => $percentages,
            'datasetSources' => $datasetSources,
            'matrix' => $analysisRun->evaluation['confusion_matrix'] ?? [],
            'metricsByLabel' => $analysisRun->evaluation['per_label'] ?? [],
            'syncOnly' => (bool) ($analysisRun->run_config['sync_only'] ?? false),
            'syncMessage' => $analysisRun->evaluation['message'] ?? null,
        ]);
    }
}
