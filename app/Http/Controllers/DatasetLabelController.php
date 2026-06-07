<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DatasetLabelController extends Controller
{
    public function index(Request $request): View
    {
        $filter = (string) $request->query('filter', 'unlabeled');

        $query = VideoComment::query()
            ->with('video')
            ->where('is_indonesian', true)
            ->orderByRaw('manual_sentiment IS NULL DESC')
            ->orderByDesc('published_at');

        if ($filter === 'unlabeled') {
            $query->whereNull('manual_sentiment');
        } elseif ($filter === 'labeled') {
            $query->whereNotNull('manual_sentiment');
        }

        return view('dataset.labels', [
            'comments' => $query->paginate(40)->withQueryString(),
            'filter' => $filter,
            'videos' => Video::query()->orderBy('title')->get(),
            'stats' => [
                'total_cached' => VideoComment::query()->count(),
                'total_indonesian' => VideoComment::query()->where('is_indonesian', true)->count(),
                'total_labeled' => VideoComment::query()->whereNotNull('manual_sentiment')->count(),
                'positif' => VideoComment::query()->where('manual_sentiment', 'positif')->count(),
                'negatif' => VideoComment::query()->where('manual_sentiment', 'negatif')->count(),
                'netral' => VideoComment::query()->where('manual_sentiment', 'netral')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'labels' => ['array'],
            'labels.*' => ['nullable', 'in:positif,negatif,netral'],
        ]);

        foreach ($validated['labels'] ?? [] as $commentId => $label) {
            VideoComment::query()
                ->whereKey((int) $commentId)
                ->update(['manual_sentiment' => $label ?: null]);
        }

        return back()->with('status', 'Label komentar berhasil disimpan.');
    }
}
