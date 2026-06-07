<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Analisis Sentimen Manchester United' }}</title>
    <style>
        :root {
            --bg: #f4efe6;
            --bg-strong: #efe6d6;
            --surface: rgba(255, 252, 246, 0.88);
            --surface-strong: #fffdf8;
            --surface-soft: #f7f0e5;
            --line: #dbcdb8;
            --line-strong: #cdbba0;
            --ink: #221d17;
            --muted: #6f6658;
            --accent: #8d1b17;
            --accent-strong: #65110f;
            --accent-soft: #f3d6cf;
            --gold: #b98932;
            --positive: #1f7a4f;
            --negative: #be2d2d;
            --neutral: #5b48c8;
            --info: #1e63d6;
            --warning: #b46b09;
            --shadow-xl: 0 28px 60px rgba(63, 43, 14, 0.12);
            --shadow-lg: 0 20px 36px rgba(63, 43, 14, 0.10);
            --shadow-sm: 0 10px 20px rgba(63, 43, 14, 0.08);
            --radius-xl: 30px;
            --radius-lg: 24px;
            --radius-md: 18px;
            --radius-sm: 14px;
        }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            color: var(--ink);
            font-family: Georgia, "Times New Roman", serif;
            background:
                radial-gradient(circle at 0% 0%, rgba(141, 27, 23, 0.14), transparent 28%),
                radial-gradient(circle at 100% 12%, rgba(185, 137, 50, 0.14), transparent 20%),
                linear-gradient(180deg, #fbf7f0 0%, #f3eadb 45%, #efe4d0 100%);
            min-height: 100vh;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255,255,255,0.18) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.18) 1px, transparent 1px);
            background-size: 32px 32px;
            mask-image: radial-gradient(circle at center, rgba(0,0,0,0.42), transparent 76%);
            opacity: 0.45;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        textarea,
        select {
            font: inherit;
        }

        .shell {
            position: relative;
            z-index: 1;
            width: min(1280px, calc(100% - 32px));
            margin: 0 auto;
            padding: 28px 0 72px;
        }

        .masthead {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 22px;
            padding: 18px 22px;
            border: 1px solid rgba(205, 187, 160, 0.9);
            border-radius: 999px;
            background: rgba(255, 252, 246, 0.72);
            backdrop-filter: blur(18px);
            box-shadow: var(--shadow-sm);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background:
                linear-gradient(135deg, var(--accent) 0%, var(--accent-strong) 100%);
            color: #fff;
            font-weight: bold;
            box-shadow: 0 14px 24px rgba(141, 27, 23, 0.28);
        }

        .brand-copy strong {
            display: block;
            font-size: 1rem;
            letter-spacing: 0.04em;
        }

        .brand-copy span {
            display: block;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .masthead-links {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .page-grid {
            display: grid;
            gap: 22px;
        }

        .hero-grid,
        .two-col,
        .stats-grid,
        .meta-grid,
        .cards-grid {
            display: grid;
            gap: 20px;
        }

        .hero-grid {
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
        }

        .two-col {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .stats-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .cards-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .panel,
        .glass-panel {
            border-radius: var(--radius-xl);
            border: 1px solid rgba(205, 187, 160, 0.92);
            box-shadow: var(--shadow-lg);
        }

        .panel {
            padding: 26px;
            background: var(--surface-strong);
        }

        .glass-panel {
            padding: 26px;
            background: var(--surface);
            backdrop-filter: blur(16px);
        }

        .panel.inset {
            background: linear-gradient(180deg, rgba(255,255,255,0.96) 0%, rgba(247,240,229,0.96) 100%);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            color: var(--accent);
            font-size: 0.8rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            font-weight: bold;
        }

        .eyebrow::before {
            content: "";
            width: 34px;
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, var(--accent) 100%);
        }

        h1, h2, h3, p {
            margin-top: 0;
        }

        h1 {
            margin-bottom: 14px;
            font-size: clamp(2.4rem, 5vw, 4.6rem);
            line-height: 0.96;
            letter-spacing: -0.04em;
        }

        h2 {
            margin-bottom: 14px;
            font-size: clamp(1.35rem, 2vw, 2rem);
            line-height: 1.05;
        }

        h3 {
            margin-bottom: 10px;
            font-size: 1.05rem;
        }

        p {
            line-height: 1.65;
            color: var(--muted);
        }

        .lead {
            max-width: 74ch;
            font-size: 1.06rem;
        }

        .muted { color: var(--muted); }

        .caption {
            font-size: 0.92rem;
            color: var(--muted);
        }

        .accent {
            color: var(--accent);
        }

        .chip,
        .pill,
        .button,
        .tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 10px 18px;
            border-radius: 999px;
            border: 1px solid transparent;
            transition: 0.18s ease;
        }

        .chip {
            min-height: 0;
            padding: 8px 12px;
            font-size: 0.85rem;
            color: #fff;
        }

        .chip.cache { background: var(--info); }
        .chip.api { background: var(--warning); }
        .chip.positive, .chip.positif { background: var(--positive); }
        .chip.negative, .chip.negatif { background: var(--negative); }
        .chip.neutral, .chip.netral { background: var(--neutral); }

        .pill,
        .tab,
        .button.secondary {
            background: rgba(243, 214, 207, 0.72);
            color: var(--accent);
            border-color: rgba(205, 187, 160, 0.86);
        }

        .button.primary,
        .tab.active {
            color: #fff;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-strong) 100%);
            box-shadow: 0 16px 28px rgba(141, 27, 23, 0.22);
        }

        .button.ghost {
            background: rgba(255,255,255,0.7);
            color: var(--ink);
            border-color: rgba(205, 187, 160, 0.86);
        }

        .button:hover,
        .pill:hover,
        .tab:hover {
            transform: translateY(-1px);
        }

        .button-row,
        .toolbar,
        .tab-row,
        .meta-stack {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .textarea,
        .field,
        .select {
            width: 100%;
            border-radius: 22px;
            border: 1px solid rgba(205, 187, 160, 0.92);
            background: rgba(255,255,255,0.9);
            color: var(--ink);
            padding: 16px 18px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.75);
        }

        .textarea {
            min-height: 220px;
            resize: vertical;
            line-height: 1.6;
        }

        .textarea:focus,
        .field:focus,
        .select:focus {
            outline: 2px solid rgba(141, 27, 23, 0.18);
            border-color: rgba(141, 27, 23, 0.48);
        }

        .label {
            display: inline-block;
            margin-bottom: 12px;
            font-size: 0.95rem;
            font-weight: bold;
            color: var(--ink);
        }

        .stats-card {
            overflow: hidden;
            position: relative;
        }

        .stats-card::after {
            content: "";
            position: absolute;
            top: 18px;
            right: 18px;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(141, 27, 23, 0.12) 0%, transparent 72%);
        }

        .stats-card h3,
        .stats-card span {
            margin: 0;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .stats-card strong {
            display: block;
            margin-top: 12px;
            font-size: clamp(2rem, 3vw, 3rem);
            line-height: 1;
        }

        .metric-card {
            display: grid;
            gap: 10px;
        }

        .metric-number {
            font-size: 2rem;
            font-weight: bold;
        }

        .split-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px;
            align-items: start;
        }

        .timeline-list,
        .feature-list,
        .stack-list {
            display: grid;
            gap: 12px;
        }

        .timeline-item,
        .feature-item {
            display: grid;
            gap: 6px;
            padding: 16px 18px;
            border: 1px solid rgba(205, 187, 160, 0.86);
            border-radius: var(--radius-md);
            background: rgba(255,255,255,0.74);
        }

        .feature-item strong,
        .timeline-item strong {
            font-size: 1rem;
        }

        .run-list,
        .stack-list {
            display: grid;
            gap: 14px;
        }

        .run-card,
        .info-card,
        .comment-card,
        .video-card {
            border-radius: var(--radius-lg);
            border: 1px solid rgba(205, 187, 160, 0.86);
            background: rgba(255,255,255,0.78);
            box-shadow: var(--shadow-sm);
        }

        .run-card,
        .info-card,
        .video-card {
            padding: 18px 20px;
        }

        .run-card:hover,
        .video-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
            border-radius: var(--radius-lg);
            border: 1px solid rgba(205, 187, 160, 0.86);
            background: rgba(255,255,255,0.78);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 640px;
        }

        th,
        td {
            padding: 14px 16px;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid rgba(219, 205, 184, 0.92);
        }

        th {
            font-size: 0.92rem;
            color: var(--muted);
            background: rgba(247, 240, 229, 0.9);
        }

        tbody tr:last-child td,
        tbody tr:last-child th {
            border-bottom: 0;
        }

        .notice,
        .alert,
        .success {
            padding: 16px 18px;
            border-radius: var(--radius-md);
            border: 1px solid;
        }

        .notice {
            background: #fff5e1;
            color: #8d620f;
            border-color: #e9d09b;
        }

        .alert {
            background: #fff0f0;
            color: #a22121;
            border-color: #ebc4c4;
        }

        .success {
            background: #edf8f0;
            color: #22633f;
            border-color: #b8dfc1;
        }

        .donut-shell {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 28px;
            align-items: center;
        }

        .donut {
            width: 240px;
            height: 240px;
            border-radius: 50%;
            position: relative;
            margin: 0 auto;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.35), var(--shadow-sm);
        }

        .donut::after {
            content: attr(data-center);
            position: absolute;
            inset: 30px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            padding: 20px;
            background: linear-gradient(180deg, #fffdf9 0%, #fbf5ec 100%);
            color: var(--muted);
            text-align: center;
            line-height: 1.3;
            white-space: pre-line;
            box-shadow: inset 0 0 0 1px rgba(219,205,184,0.8);
        }

        .bar-stack {
            display: grid;
            gap: 14px;
        }

        .bar-label {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 8px;
        }

        .bar-track {
            height: 14px;
            border-radius: 999px;
            overflow: hidden;
            background: #ecdfca;
        }

        .bar-fill {
            height: 100%;
            border-radius: inherit;
        }

        .bar-fill.positif { background: linear-gradient(90deg, #1f7a4f 0%, #35a36d 100%); }
        .bar-fill.negatif { background: linear-gradient(90deg, #be2d2d 0%, #de5d5d 100%); }
        .bar-fill.netral { background: linear-gradient(90deg, #5b48c8 0%, #8a72f5 100%); }

        .meta-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .comment-card {
            padding: 18px;
            display: grid;
            grid-template-columns: 250px minmax(0, 1.05fr) minmax(0, 0.9fr) 250px;
            gap: 16px;
        }

        .comment-box {
            height: 100%;
            padding: 16px;
            border-radius: var(--radius-md);
            border: 1px solid rgba(205, 187, 160, 0.82);
            background: var(--surface-soft);
        }

        .comment-number {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            margin-bottom: 12px;
            border-radius: 16px;
            color: #fff;
            font-weight: bold;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-strong) 100%);
            box-shadow: 0 14px 20px rgba(141, 27, 23, 0.2);
        }

        .label-options {
            display: grid;
            gap: 10px;
        }

        .label-option {
            position: relative;
        }

        .label-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .label-option span {
            display: block;
            padding: 14px 16px;
            text-align: center;
            border-radius: var(--radius-md);
            border: 1px solid rgba(205, 187, 160, 0.86);
            background: rgba(255,255,255,0.84);
            transition: 0.18s ease;
        }

        .label-option input:checked + span {
            color: #fff;
            border-color: transparent;
            box-shadow: var(--shadow-sm);
        }

        .label-option.positive input:checked + span { background: var(--positive); }
        .label-option.negative input:checked + span { background: var(--negative); }
        .label-option.neutral input:checked + span { background: var(--neutral); }
        .label-option.empty input:checked + span { background: #8d8374; }

        .sticky-bar {
            position: sticky;
            bottom: 18px;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 20px;
            border-radius: calc(var(--radius-lg) + 2px);
            border: 1px solid rgba(205, 187, 160, 0.92);
            background: rgba(255, 252, 246, 0.84);
            backdrop-filter: blur(18px);
            box-shadow: var(--shadow-lg);
        }

        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: var(--muted);
        }

        .pagination {
            margin-top: 22px;
        }

        .pagination nav {
            display: flex;
            justify-content: center;
        }

        .pagination svg {
            width: 18px;
            height: 18px;
        }

        .pagination > nav > div:first-child {
            display: none;
        }

        .pagination span[aria-current="page"] > span,
        .pagination a,
        .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            min-height: 42px;
            margin: 0 4px;
            padding: 0 14px;
            border-radius: 999px;
            border: 1px solid rgba(205, 187, 160, 0.86);
            background: rgba(255,255,255,0.78);
            color: var(--ink);
        }

        .pagination span[aria-current="page"] > span {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-strong) 100%);
            color: #fff;
            border-color: transparent;
        }

        @media (max-width: 1180px) {
            .hero-grid,
            .two-col,
            .donut-shell,
            .meta-grid,
            .comment-card {
                grid-template-columns: 1fr;
            }

            .cards-grid {
                grid-template-columns: 1fr 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 720px) {
            .shell {
                width: min(100% - 20px, 1280px);
                padding-top: 18px;
            }

            .masthead {
                border-radius: 28px;
                align-items: flex-start;
                flex-direction: column;
            }

            .cards-grid,
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .sticky-bar {
                align-items: stretch;
                flex-direction: column;
            }

            .button,
            .pill,
            .tab {
                width: 100%;
            }

            .button-row,
            .toolbar,
            .tab-row {
                display: grid;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="shell">
        <header class="masthead">
            <div class="brand">
                <div class="brand-mark">MU</div>
                <div class="brand-copy">
                    <strong>Sentiment Analytics</strong>
                    <span>Dashboard penelitian komentar YouTube Manchester United</span>
                </div>
            </div>
            <div class="masthead-links">
                <a class="pill" href="{{ route('analysis.index') }}">Dashboard</a>
                <a class="pill" href="{{ route('dataset.labels') }}">Label Dataset</a>
            </div>
        </header>

        <main class="page-grid">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
