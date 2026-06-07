@extends('layouts.app')

@php($title = 'Pelabelan Dataset Komentar YouTube')

@section('content')
    <section class="hero-grid">
        <article class="glass-panel">
            <div class="toolbar" style="margin-bottom:16px;">
                <a class="pill" href="{{ route('analysis.index') }}">Kembali ke dashboard</a>
            </div>
            <div class="eyebrow">Ground Truth Builder</div>
            <h1>Pelabelan dataset komentar yang lebih cepat, lebih rapi, dan konsisten.</h1>
            <p class="lead">
                Halaman ini dipakai untuk memberi label manual pada komentar berbahasa Indonesia. Label inilah yang
                menjadi ground truth untuk training dan evaluasi model skripsi.
            </p>
            <div class="tab-row" style="margin-top:18px;">
                <a class="tab {{ $filter === 'unlabeled' ? 'active' : '' }}" href="{{ route('dataset.labels', ['filter' => 'unlabeled']) }}">Belum dilabel</a>
                <a class="tab {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('dataset.labels', ['filter' => 'all']) }}">Semua</a>
                <a class="tab {{ $filter === 'labeled' ? 'active' : '' }}" href="{{ route('dataset.labels', ['filter' => 'labeled']) }}">Sudah dilabel</a>
            </div>
        </article>

        <aside class="panel inset">
            <div class="eyebrow">Panduan</div>
            <div class="feature-list">
                <div class="feature-item">
                    <strong class="accent">Positif</strong>
                    <span class="caption">Komentar berisi dukungan, pujian, optimisme, atau apresiasi terhadap tim, pemain, atau pelatih.</span>
                </div>
                <div class="feature-item">
                    <strong style="color: var(--negative);">Negatif</strong>
                    <span class="caption">Komentar berisi kritik, hinaan, sindiran, ejekan, atau kekecewaan yang tegas.</span>
                </div>
                <div class="feature-item">
                    <strong style="color: var(--neutral);">Netral</strong>
                    <span class="caption">Komentar informatif, candaan tanpa polaritas tegas, atau opini yang tidak condong ke kelas tertentu.</span>
                </div>
            </div>
        </aside>
    </section>

    <section class="cards-grid">
        <article class="panel stats-card">
            <span>Cache total</span>
            <strong>{{ number_format($stats['total_cached']) }}</strong>
        </article>
        <article class="panel stats-card">
            <span>Bahasa Indonesia</span>
            <strong>{{ number_format($stats['total_indonesian']) }}</strong>
        </article>
        <article class="panel stats-card">
            <span>Sudah dilabel</span>
            <strong>{{ number_format($stats['total_labeled']) }}</strong>
        </article>
        <article class="panel stats-card">
            <span>Positif</span>
            <strong>{{ number_format($stats['positif']) }}</strong>
        </article>
        <article class="panel stats-card">
            <span>Negatif</span>
            <strong>{{ number_format($stats['negatif']) }}</strong>
        </article>
        <article class="panel stats-card">
            <span>Netral</span>
            <strong>{{ number_format($stats['netral']) }}</strong>
        </article>
    </section>

    <section class="panel">
        @if (session('status'))
            <div class="success" style="margin-bottom:18px;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('dataset.labels.store') }}">
            @csrf

            <div class="sticky-bar">
                <div>
                    <strong>Filter aktif: {{ $filter === 'unlabeled' ? 'Belum dilabel' : ($filter === 'labeled' ? 'Sudah dilabel' : 'Semua') }}</strong>
                    <div class="caption" style="margin-top:4px;">
                        Menampilkan item {{ $comments->firstItem() ?? 0 }} - {{ $comments->lastItem() ?? 0 }} dari {{ $comments->total() }} komentar.
                        Label komentar di halaman ini lalu simpan sekaligus.
                    </div>
                </div>
                <button class="button primary" type="submit">Simpan Label</button>
            </div>

            <div class="stack-list" style="margin-top:18px;">
                @forelse ($comments as $comment)
                    <article class="comment-card">
                        <div>
                            <div class="comment-number">{{ $comments->firstItem() + $loop->index }}</div>
                            <strong>{{ $comment->video?->title ?? '-' }}</strong>
                            <p class="caption" style="margin:12px 0 0;">
                                {{ $comment->video?->youtube_video_id ?? '-' }}<br>
                                {{ $comment->published_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-' }} WIB
                            </p>
                        </div>

                        <div class="comment-box">
                            <h3>Komentar Asli</h3>
                            <div>{{ $comment->original_text }}</div>
                        </div>

                        <div class="comment-box">
                            <h3>Hasil Preprocessing</h3>
                            <div>{{ $comment->processed_text ?: '-' }}</div>
                        </div>

                        <div class="comment-box">
                            <h3>Pilih Label</h3>
                            <div class="label-options">
                                <label class="label-option positive">
                                    <input type="radio" name="labels[{{ $comment->id }}]" value="positif" @checked($comment->manual_sentiment === 'positif')>
                                    <span>Positif</span>
                                </label>
                                <label class="label-option negative">
                                    <input type="radio" name="labels[{{ $comment->id }}]" value="negatif" @checked($comment->manual_sentiment === 'negatif')>
                                    <span>Negatif</span>
                                </label>
                                <label class="label-option neutral">
                                    <input type="radio" name="labels[{{ $comment->id }}]" value="netral" @checked($comment->manual_sentiment === 'netral')>
                                    <span>Netral</span>
                                </label>
                                <label class="label-option empty">
                                    <input type="radio" name="labels[{{ $comment->id }}]" value="" @checked($comment->manual_sentiment === null)>
                                    <span>Kosongkan</span>
                                </label>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        Tidak ada komentar pada filter ini.
                    </div>
                @endforelse
            </div>

            <div class="sticky-bar" style="margin-top:20px;">
                <div>
                    <strong>Selesai melabel halaman ini?</strong>
                    <div class="caption" style="margin-top:4px;">Simpan terlebih dahulu, lalu lanjut ke halaman berikutnya.</div>
                </div>
                <button class="button primary" type="submit">Simpan Label</button>
            </div>
        </form>

        <div class="pagination">
            {{ $comments->links() }}
        </div>
    </section>
@endsection
