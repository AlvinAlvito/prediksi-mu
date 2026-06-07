@extends('layouts.app')

@php($title = 'Hasil Analisis #' . $run->id)

@section('content')
    @php($centerText = $run->analyzed_comments . " komentar\nteranalisis")

    <section class="hero-grid">
        <article class="glass-panel">
            <div class="toolbar" style="margin-bottom:16px;">
                <a class="pill" href="{{ route('analysis.index') }}">Kembali ke dashboard</a>
            </div>
            <div class="eyebrow">Run #{{ $run->id }}</div>
            <h1>Hasil analisis sentimen dari dataset yang sudah tersimpan.</h1>
            <p class="lead">
                Run ini dibuat pada {{ $run->created_at?->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB melalui
                {{ $run->source }}. Sistem menampilkan ringkasan distribusi sentimen, metrik evaluasi, confusion matrix,
                serta contoh komentar hasil klasifikasi.
            </p>
            <div class="button-row" style="margin-top:18px;">
                @foreach ($datasetSources as $source)
                    <span class="chip {{ $source === 'database_cache' ? 'cache' : 'api' }}">
                        {{ $source === 'database_cache' ? 'Database Cache' : 'YouTube API' }}
                    </span>
                @endforeach
            </div>
        </article>

        <aside class="panel inset">
            <div class="eyebrow">Status Run</div>
            <div class="feature-list">
                <div class="feature-item">
                    <strong>Sumber dataset</strong>
                    <span class="caption">{{ $datasetSources->contains('database_cache') ? 'Menggunakan cache lokal database.' : 'Menggunakan hasil panggilan API YouTube.' }}</span>
                </div>
                <div class="feature-item">
                    <strong>Status analisis</strong>
                    <span class="caption">{{ $syncOnly ? 'Run sinkronisasi dataset tanpa analisis final.' : 'Run evaluasi dan klasifikasi selesai.' }}</span>
                </div>
                <div class="feature-item">
                    <strong>Konfigurasi model</strong>
                    <span class="caption">{{ strtoupper($run->feature_extractor ?? 'tf_idf') }} + {{ strtoupper($run->algorithm ?? 'naive_bayes') }}</span>
                </div>
            </div>
        </aside>
    </section>

    @if ($syncOnly)
        <div class="notice">
            {{ $syncMessage ?? 'Run ini hanya memperbarui dataset cache dari YouTube API. Analisis final baru bisa dijalankan setelah komentar diberi label manual.' }}
        </div>
    @endif

    <section class="stats-grid">
        <article class="panel stats-card">
            <h3>Total komentar diambil</h3>
            <strong>{{ number_format($run->total_comments) }}</strong>
        </article>
        <article class="panel stats-card">
            <h3>Komentar dianalisis</h3>
            <strong>{{ number_format($run->analyzed_comments) }}</strong>
        </article>
        <article class="panel stats-card">
            <h3>Akurasi model</h3>
            <strong>{{ number_format(($run->accuracy ?? 0) * 100, 2) }}%</strong>
        </article>
        <article class="panel stats-card">
            <h3>F1-score</h3>
            <strong>{{ number_format(($run->f1_score ?? 0) * 100, 2) }}%</strong>
        </article>
    </section>

    <section class="two-col">
        <article class="panel">
            <div class="eyebrow">Distribusi</div>
            <h2>Sentimen Komentar</h2>
            <div class="donut-shell">
                <div
                    class="donut"
                    data-center="{{ $centerText }}"
                    style="background: conic-gradient(
                        var(--positive) 0deg {{ ($percentages['positif'] ?? 0) * 3.6 }}deg,
                        var(--negative) {{ ($percentages['positif'] ?? 0) * 3.6 }}deg {{ (($percentages['positif'] ?? 0) + ($percentages['negatif'] ?? 0)) * 3.6 }}deg,
                        var(--neutral) {{ (($percentages['positif'] ?? 0) + ($percentages['negatif'] ?? 0)) * 3.6 }}deg 360deg
                    );">
                </div>

                <div class="bar-stack">
                    @foreach ($distribution as $label => $value)
                        <div>
                            <div class="bar-label">
                                <strong>{{ ucfirst($label) }}</strong>
                                <span class="caption">{{ number_format($value) }} | {{ number_format($percentages[$label] ?? 0, 2) }}%</span>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill {{ $label }}" style="width: {{ min(100, $percentages[$label] ?? 0) }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </article>

        <article class="panel inset">
            <div class="eyebrow">Sumber Video</div>
            <h2>Metadata Video</h2>
            <div class="meta-grid">
                @foreach ($run->video_metadata ?? [] as $video)
                    <div class="video-card">
                        <strong>{{ $video['title'] ?? $video['video_id'] }}</strong>
                        <p class="caption" style="margin:10px 0 0;">
                            {{ $video['channel_title'] ?? '-' }}<br>
                            Video ID: {{ $video['video_id'] ?? '-' }}<br>
                            Komentar publik: {{ number_format($video['comment_count'] ?? 0) }}<br>
                            Komentar dataset lokal: {{ number_format($video['cached_comment_count'] ?? 0) }}
                            @if (!empty($video['last_comments_synced_at']))
                                <br>Terakhir sinkron:
                                {{ \Illuminate\Support\Carbon::parse($video['last_comments_synced_at'])->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
                            @endif
                        </p>
                        <div class="button-row" style="margin-top:14px;">
                            <span class="chip {{ ($video['dataset_source'] ?? '') === 'database_cache' ? 'cache' : 'api' }}">
                                {{ ($video['dataset_source'] ?? '') === 'database_cache' ? 'Pakai cache DB' : 'Ambil dari API' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="two-col">
        <article class="panel">
            <div class="eyebrow">Evaluasi</div>
            <h2>Metrik Per Kelas</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th>Precision</th>
                            <th>Recall</th>
                            <th>F1-score</th>
                            <th>Support</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($metricsByLabel as $label => $metric)
                            <tr>
                                <td><span class="chip {{ $label }}">{{ ucfirst($label) }}</span></td>
                                <td>{{ number_format(($metric['precision'] ?? 0) * 100, 2) }}%</td>
                                <td>{{ number_format(($metric['recall'] ?? 0) * 100, 2) }}%</td>
                                <td>{{ number_format(($metric['f1_score'] ?? 0) * 100, 2) }}%</td>
                                <td>{{ $metric['support'] ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="caption" style="margin-top:14px;">
                Evaluasi model dihitung dari komentar YouTube berlabel manual yang difilter ke Bahasa Indonesia, lalu dibagi menjadi data latih dan data uji.
            </p>
        </article>

        <article class="panel inset">
            <div class="eyebrow">Validasi</div>
            <h2>Confusion Matrix</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Aktual \ Prediksi</th>
                            @foreach (array_keys($matrix) as $label)
                                <th>{{ ucfirst($label) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($matrix as $actual => $row)
                            <tr>
                                <th>{{ ucfirst($actual) }}</th>
                                @foreach ($row as $value)
                                    <td>{{ $value }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <section class="panel">
        <div class="split-card">
            <div>
                <div class="eyebrow">Sampel Output</div>
                <h2>Contoh Komentar Hasil Klasifikasi</h2>
            </div>
            <div class="pill">50 komentar terbaru pada run ini</div>
        </div>

        <div class="table-wrap" style="margin-top:18px;">
            <table>
                <thead>
                    <tr>
                        <th>Video</th>
                        <th>Komentar Asli</th>
                        <th>Preprocessing</th>
                        <th>Aktual</th>
                        <th>Prediksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($run->comments as $comment)
                        <tr>
                            <td>{{ $comment->video_id }}</td>
                            <td>{{ $comment->original_text }}</td>
                            <td>{{ $comment->processed_text }}</td>
                            <td>{{ $comment->actual_sentiment ?? '-' }}</td>
                            <td><span class="chip {{ $comment->predicted_sentiment }}">{{ ucfirst($comment->predicted_sentiment) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
