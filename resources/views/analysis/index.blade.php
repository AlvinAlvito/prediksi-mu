@extends('layouts.app')

@php($title = 'Analisis Sentimen Manchester United')

@section('content')
    <section class="hero-grid">
        <article class="glass-panel">
            <div class="eyebrow">Sentiment Dashboard</div>
            <h1>Analisis komentar YouTube dengan alur riset yang siap dipakai ulang.</h1>
            <p class="lead">
                Sistem ini mengambil komentar video Manchester United dari YouTube, menyimpan dataset ke cache lokal,
                memfasilitasi pelabelan manual, lalu menjalankan preprocessing, TF-IDF, Naive Bayes, dan evaluasi model
                dalam satu alur kerja yang konsisten.
            </p>
            <div class="button-row" style="margin-top:18px;">
                <a class="button primary" href="{{ route('dataset.labels') }}">Buka label dataset</a>
                <span class="pill">2 jalur penggunaan: web UI dan cache lokal</span>
            </div>
        </article>

        <aside class="panel inset">
            <div class="eyebrow">Pipeline</div>
            <div class="timeline-list">
                <div class="timeline-item">
                    <strong>1. Crawling dan cache dataset</strong>
                    <span class="caption">Komentar diambil dari YouTube API v3 lalu disimpan ke database lokal.</span>
                </div>
                <div class="timeline-item">
                    <strong>2. Preprocessing Bahasa Indonesia</strong>
                    <span class="caption">Cleaning, tokenizing, stopword removal, stemming Sastrawi, dan filter bahasa.</span>
                </div>
                <div class="timeline-item">
                    <strong>3. Pelabelan dan pembentukan model</strong>
                    <span class="caption">Komentar berlabel manual dipakai untuk training dan testing model.</span>
                </div>
                <div class="timeline-item">
                    <strong>4. Analisis dan evaluasi</strong>
                    <span class="caption">Sistem menampilkan distribusi sentimen, confusion matrix, dan metrik evaluasi.</span>
                </div>
            </div>
        </aside>
    </section>

    <section class="two-col">
        <article class="panel">
            <div class="eyebrow">Eksekusi</div>
            <h2>Jalankan Analisis</h2>
            <form method="POST" action="{{ route('analysis.store') }}">
                @csrf
                <label class="label" for="links">Link YouTube</label>
                <textarea class="textarea" id="links" name="links" placeholder="Satu link per baris">{{ old('links', $defaultLinks) }}</textarea>
                <div class="button-row" style="margin-top:18px;">
                    <button class="button primary" type="submit" name="mode" value="manual">Analisis dari textarea</button>
                    <button class="button secondary" type="submit" name="mode" value="cache">Analisis dari cache</button>
                    <button class="button secondary" type="submit" name="mode" value="refresh">Refresh dari YouTube API</button>
                    <button class="button ghost" type="submit" name="mode" value="default">Pakai link default</button>
                </div>
            </form>

            @if ($errors->any())
                <div class="alert" style="margin-top:18px;">
                    {{ $errors->first() }}
                </div>
            @endif
        </article>

        <article class="panel inset">
            <div class="eyebrow">Ringkas</div>
            <h2>Cara Pakai Sistem</h2>
            <div class="feature-list">
                <div class="feature-item">
                    <strong>Refresh dataset sekali</strong>
                    <span class="caption">Gunakan tombol refresh untuk menyinkronkan komentar dari dua video YouTube.</span>
                </div>
                <div class="feature-item">
                    <strong>Label komentar dari cache</strong>
                    <span class="caption">Komentar berbahasa Indonesia diberi label manual sebagai ground truth penelitian.</span>
                </div>
                <div class="feature-item">
                    <strong>Analisis dari cache</strong>
                    <span class="caption">Model dijalankan memakai dataset lokal agar lebih cepat dan hemat kuota API.</span>
                </div>
                <div class="feature-item">
                    <strong>Tinjau hasil run</strong>
                    <span class="caption">Setiap run menyimpan metrik, confusion matrix, distribusi sentimen, dan contoh komentar.</span>
                </div>
            </div>
        </article>
    </section>

    <section class="panel">
        <div class="split-card">
            <div>
                <div class="eyebrow">Riwayat Run</div>
                <h2>Analisis Terakhir</h2>
                <p class="caption">Lima run terbaru ditampilkan di bawah untuk memudahkan perbandingan hasil dan sumber dataset.</p>
            </div>
            <div class="pill">Histori tersimpan otomatis</div>
        </div>

        <div class="run-list" style="margin-top:18px;">
            @forelse ($recentRuns as $run)
                <a class="run-card" href="{{ route('analysis.show', $run) }}">
                    <div class="split-card">
                        <div>
                            <strong>Run #{{ $run->id }}</strong>
                            <p class="caption" style="margin:6px 0 0;">
                                {{ $run->created_at?->format('d M Y H:i') }} • {{ $run->source }}
                            </p>
                        </div>
                        <div class="meta-stack">
                            <span class="chip cache">Komentar {{ $run->analyzed_comments }}</span>
                            <span class="chip api">Akurasi {{ number_format(($run->accuracy ?? 0) * 100, 2) }}%</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="empty-state">
                    Belum ada hasil analisis tersimpan.
                </div>
            @endforelse
        </div>
    </section>
@endsection
