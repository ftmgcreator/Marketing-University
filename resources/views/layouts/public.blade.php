<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TISU Marketing — Statistika')</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        :root {
            --bg: #07090d;
            --bg-elevated: #0e1117;
            --surface: #14171f;
            --surface-2: #1a1f2b;
            --surface-3: #232938;
            --surface-hover: #1f2532;
            --border: rgba(255,255,255,0.05);
            --border-strong: rgba(255,255,255,0.08);
            --border-glow: rgba(99,102,241,0.25);
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-bright: #a5b4fc;
            --primary-soft: rgba(99,102,241,0.1);
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
            --success: #10b981;
            --success-bright: #34d399;
            --success-bg: rgba(16,185,129,0.08);
            --danger: #ef4444;
            --danger-bright: #f87171;
            --danger-bg: rgba(239,68,68,0.08);
            --warning: #f59e0b;
            --warning-bright: #fbbf24;
            --warning-bg: rgba(245,158,11,0.08);
        }
        html, body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-feature-settings: 'cv02', 'cv03', 'cv04', 'cv11', 'ss01';
            line-height: 1.5;
        }
        body::before {
            content: '';
            position: fixed; inset: 0; z-index: -1; pointer-events: none;
            background:
                radial-gradient(ellipse 90% 60% at 50% -10%, rgba(99,102,241,0.18), transparent 50%),
                radial-gradient(ellipse 60% 50% at 100% 100%, rgba(16,185,129,0.05), transparent 60%),
                radial-gradient(ellipse 60% 50% at 0% 100%, rgba(239,68,68,0.04), transparent 60%);
        }
        body::after {
            content: '';
            position: fixed; inset: 0; z-index: -1; pointer-events: none;
            background-image:
                linear-gradient(rgba(255,255,255,0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.015) 1px, transparent 1px);
            background-size: 40px 40px;
            mask-image: radial-gradient(ellipse at center, black 30%, transparent 70%);
            -webkit-mask-image: radial-gradient(ellipse at center, black 30%, transparent 70%);
        }
        a { color: inherit; text-decoration: none; transition: color 0.15s ease; }
        a:hover { color: var(--primary-bright); }
        .container { max-width: 1340px; margin: 0 auto; padding: 0 1.5rem; }

        header.site {
            position: sticky; top: 0; z-index: 50;
            background: rgba(7,9,13,0.75);
            backdrop-filter: saturate(200%) blur(24px);
            -webkit-backdrop-filter: saturate(200%) blur(24px);
            border-bottom: 1px solid var(--border-strong);
        }
        .site-inner { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 0; }
        .brand { display: flex; align-items: center; gap: 0.85rem; font-weight: 700; font-size: 1.05rem; }
        .brand img {
            width: 40px; height: 40px; object-fit: contain;
            filter: drop-shadow(0 6px 14px rgba(99,102,241,0.45));
        }
        .brand-text { display: flex; flex-direction: column; line-height: 1.15; }
        .brand-text .name {
            background: linear-gradient(135deg, #fff 0%, #c7d2fe 100%);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
            letter-spacing: -0.015em;
        }
        .brand-text .tag {
            font-size: 0.7rem; color: var(--text-dim);
            font-weight: 500; letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .header-meta { display: flex; align-items: center; gap: 0.75rem; }
        .report-tag {
            display: inline-flex; align-items: center; gap: 0.55rem;
            font-size: 0.78rem; font-weight: 500;
            color: var(--text-muted);
            background: var(--surface);
            padding: 0.5rem 0.95rem;
            border-radius: 99px;
            border: 1px solid var(--border-strong);
        }
        .report-tag .dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--success);
            box-shadow: 0 0 0 3px rgba(16,185,129,0.18), 0 0 12px var(--success);
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }

        main { padding: 2.5rem 0 5rem; }

        .page-head { margin-bottom: 2.25rem; text-align: center; }
        h1.page-title {
            font-size: 2.25rem; margin: 0 0 0.6rem; font-weight: 800;
            letter-spacing: -0.03em; line-height: 1.1;
            background: linear-gradient(180deg, #fff 0%, #c7d2fe 100%);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
        }
        .page-subtitle {
            color: var(--text-muted); margin: 0 auto;
            font-size: 0.98rem; max-width: 640px;
        }

        .breadcrumbs {
            display: flex; align-items: center; gap: 0.5rem;
            color: var(--text-muted); font-size: 0.82rem;
            margin-bottom: 1.5rem; flex-wrap: wrap;
            justify-content: center;
        }
        .breadcrumbs a { color: var(--text-muted); }
        .breadcrumbs a:hover { color: var(--primary-bright); }
        .breadcrumbs .sep { opacity: 0.3; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem; margin-bottom: 2.5rem;
        }
        .stat-card {
            background: linear-gradient(180deg, var(--surface) 0%, var(--bg-elevated) 100%);
            border: 1px solid var(--border-strong);
            border-radius: 1.1rem;
            padding: 1.6rem 1.4rem;
            position: relative; overflow: hidden;
            text-align: center;
            transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .stat-card::before {
            content: ''; position: absolute;
            top: -1px; left: 50%; transform: translateX(-50%);
            width: 60%; height: 1px;
            background: linear-gradient(90deg, transparent, currentColor, transparent);
            opacity: 0.5;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px -20px rgba(0,0,0,0.5);
        }
        .stat-card .stat-icon {
            margin: 0 auto 1rem;
            width: 52px; height: 52px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 0.85rem;
            background: var(--surface-2);
            position: relative;
        }
        .stat-card .stat-icon::after {
            content: ''; position: absolute; inset: 0;
            border-radius: inherit;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
        }
        .stat-card .stat-icon svg { width: 24px; height: 24px; }
        .stat-card .label {
            font-size: 0.72rem; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.08em;
            margin-bottom: 0.6rem; font-weight: 600;
        }
        .stat-card .value {
            font-size: 1.85rem; font-weight: 800;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.025em;
            line-height: 1;
            margin-bottom: 0.55rem;
        }
        .stat-card .value .currency {
            font-size: 0.72rem; color: var(--text-dim);
            font-weight: 500; margin-left: 0.25rem;
            letter-spacing: 0;
        }
        .stat-card .sub {
            font-size: 0.78rem; color: var(--text-dim);
            display: flex; align-items: center; justify-content: center;
            gap: 0.4rem; flex-wrap: wrap;
        }
        .stat-card.primary { color: var(--primary-light); border-color: rgba(99,102,241,0.2); }
        .stat-card.primary .stat-icon { background: var(--primary-soft); color: var(--primary-bright); }
        .stat-card.primary .value { color: #c7d2fe; }
        .stat-card.success { color: var(--success); border-color: rgba(16,185,129,0.2); }
        .stat-card.success .stat-icon { background: var(--success-bg); color: var(--success); }
        .stat-card.success .value { color: var(--success-bright); }
        .stat-card.danger { color: var(--danger); border-color: rgba(239,68,68,0.2); }
        .stat-card.danger .stat-icon { background: var(--danger-bg); color: var(--danger); }
        .stat-card.danger .value { color: var(--danger-bright); }
        .stat-card.neutral { color: var(--text-muted); }
        .stat-card.neutral .stat-icon { background: var(--surface-2); color: var(--text); }

        .card {
            background: var(--surface);
            border: 1px solid var(--border-strong);
            border-radius: 1.1rem;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 0 rgba(255,255,255,0.03) inset;
        }
        .card-header {
            padding: 1.15rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            flex-wrap: wrap;
            background: linear-gradient(180deg, rgba(255,255,255,0.012) 0%, transparent 100%);
        }
        .card-header h2 {
            margin: 0; font-size: 1.05rem; font-weight: 600;
            display: flex; align-items: center; gap: 0.65rem;
            letter-spacing: -0.01em;
        }
        .card-header h2 svg { width: 18px; height: 18px; opacity: 0.65; }
        .card-header .meta {
            color: var(--text-muted); font-size: 0.78rem; font-weight: 500;
            display: inline-flex; align-items: center; gap: 0.4rem;
            background: var(--surface-2);
            padding: 0.35rem 0.8rem;
            border-radius: 99px;
            border: 1px solid var(--border);
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        table.data th, table.data td {
            padding: 0.95rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        table.data th {
            background: var(--bg-elevated);
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            position: sticky; top: 0;
            border-bottom: 1px solid var(--border-strong);
            z-index: 1;
        }
        table.data tbody tr {
            transition: background 0.12s ease;
        }
        table.data tbody tr:hover { background: var(--surface-hover); }
        table.data tbody tr:last-child td { border-bottom: 0; }
        table.data td.left, table.data th.left { text-align: left; }
        table.data td.num, table.data th.num {
            text-align: center;
            font-variant-numeric: tabular-nums;
            font-feature-settings: 'tnum';
        }
        table.data td.idx {
            color: var(--text-dim);
            font-size: 0.8rem; font-weight: 500;
            width: 1%; white-space: nowrap;
            font-family: 'JetBrains Mono', monospace;
        }
        table.data a.row-link {
            color: #e0e7ff; font-weight: 500;
            display: inline-flex; align-items: center; gap: 0.45rem;
        }
        table.data a.row-link::after {
            content: '→';
            opacity: 0; transform: translateX(-6px);
            transition: all 0.18s ease;
            color: var(--primary-bright);
            font-weight: 600;
        }
        table.data tbody tr:hover a.row-link::after { opacity: 1; transform: translateX(0); }
        table.data .muted { color: var(--text-dim); font-size: 0.82rem; }
        table.data .name-cell {
            display: inline-flex; align-items: center; gap: 0.65rem;
        }
        table.data .name-cell .avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--surface-2);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.72rem; font-weight: 700;
            color: var(--primary-bright);
            border: 1px solid var(--border-strong);
            flex-shrink: 0;
        }

        .badge {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.3rem 0.65rem;
            border-radius: 99px;
            font-size: 0.72rem;
            font-weight: 600;
            background: var(--surface-2);
            font-variant-numeric: tabular-nums;
            border: 1px solid transparent;
            line-height: 1;
        }
        .badge.success { background: var(--success-bg); color: var(--success-bright); border-color: rgba(16,185,129,0.2); }
        .badge.danger { background: var(--danger-bg); color: var(--danger-bright); border-color: rgba(239,68,68,0.2); }
        .badge.warning { background: var(--warning-bg); color: var(--warning-bright); border-color: rgba(245,158,11,0.2); }
        .badge.primary { background: var(--primary-soft); color: var(--primary-bright); border-color: rgba(99,102,241,0.2); }
        .badge.neutral { background: var(--surface-2); color: var(--text-muted); border-color: var(--border-strong); }

        .ring {
            position: relative;
            width: 56px; height: 56px;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
        .ring circle {
            fill: none;
            stroke-width: 5;
            stroke-linecap: round;
        }
        .ring .track { stroke: rgba(255,255,255,0.06); }
        .ring .bar {
            transition: stroke-dashoffset 0.6s cubic-bezier(0.4,0,0.2,1);
            filter: drop-shadow(0 0 4px currentColor);
        }
        .ring .bar.success { stroke: var(--success-bright); color: var(--success); }
        .ring .bar.warning { stroke: var(--warning-bright); color: var(--warning); }
        .ring .bar.danger  { stroke: var(--danger-bright);  color: var(--danger);  }
        .ring .pct {
            position: absolute;
            font-size: 0.74rem;
            font-weight: 700;
            color: var(--text);
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.02em;
        }

        .rank {
            display: inline-flex; align-items: center; justify-content: center;
            width: 30px; height: 30px;
            border-radius: 50%;
            font-weight: 700; font-size: 0.78rem;
            font-family: 'JetBrains Mono', monospace;
            background: var(--surface-2);
            color: var(--text-dim);
            border: 1px solid var(--border-strong);
            position: relative;
        }
        .rank.gold {
            background: linear-gradient(135deg, #fef3c7 0%, #f59e0b 100%);
            color: #422006;
            border-color: rgba(245,158,11,0.5);
            box-shadow: 0 0 0 3px rgba(245,158,11,0.12), 0 6px 16px -4px rgba(245,158,11,0.5);
        }
        .rank.silver {
            background: linear-gradient(135deg, #f1f5f9 0%, #94a3b8 100%);
            color: #1e293b;
            border-color: rgba(148,163,184,0.5);
            box-shadow: 0 0 0 3px rgba(148,163,184,0.12), 0 6px 16px -4px rgba(148,163,184,0.5);
        }
        .rank.bronze {
            background: linear-gradient(135deg, #fed7aa 0%, #c2410c 100%);
            color: #431407;
            border-color: rgba(194,65,12,0.5);
            box-shadow: 0 0 0 3px rgba(194,65,12,0.12), 0 6px 16px -4px rgba(194,65,12,0.45);
        }
        .rank.gold::after, .rank.silver::after, .rank.bronze::after {
            content: '★';
            position: absolute; top: -7px; right: -7px;
            font-size: 0.6rem; line-height: 1;
            width: 16px; height: 16px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%;
            background: var(--bg);
            border: 1px solid currentColor;
        }
        .rank.gold::after   { color: #fbbf24; }
        .rank.silver::after { color: #cbd5e1; }
        .rank.bronze::after { color: #fb923c; }

        .filter-tabs { display: flex; gap: 0.4rem; flex-wrap: wrap; justify-content: center; }
        .filter-tabs a {
            padding: 0.5rem 1rem;
            border-radius: 0.6rem;
            font-size: 0.82rem;
            background: var(--surface-2);
            color: var(--text-muted);
            border: 1px solid var(--border-strong);
            font-weight: 500;
            transition: all 0.15s ease;
        }
        .filter-tabs a:hover { background: var(--surface-hover); color: var(--text); }
        .filter-tabs a.active {
            background: var(--primary-soft);
            color: var(--primary-bright);
            border-color: rgba(99,102,241,0.4);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.08), 0 4px 12px -2px rgba(99,102,241,0.25);
        }

        .empty-state { text-align: center; padding: 5rem 1rem; color: var(--text-muted); }
        .empty-state h2 { color: var(--text); margin: 0.5rem 0; font-weight: 600; }

        .table-wrap { overflow-x: auto; max-height: 75vh; }
        .table-wrap::-webkit-scrollbar { width: 10px; height: 10px; }
        .table-wrap::-webkit-scrollbar-track { background: transparent; }
        .table-wrap::-webkit-scrollbar-thumb { background: var(--surface-2); border-radius: 99px; }
        .table-wrap::-webkit-scrollbar-thumb:hover { background: var(--surface-3); }

        .money { font-variant-numeric: tabular-nums; font-feature-settings: 'tnum'; }
        .money .currency { font-size: 0.7rem; color: var(--text-dim); margin-left: 0.2rem; font-weight: 400; }

        @media (max-width: 768px) {
            h1.page-title { font-size: 1.6rem; }
            .container { padding: 0 1rem; }
            table.data th, table.data td { padding: 0.7rem 0.85rem; font-size: 0.78rem; }
            .stat-card .value { font-size: 1.4rem; }
            .stat-card { padding: 1.2rem 1rem; }
            .card-header { padding: 0.9rem 1rem; }
            .brand-text .tag { display: none; }
            .progress-cell { min-width: auto; gap: 0.4rem; }
            .progress { width: 60px; }
        }
    </style>
</head>
<body>
    <header class="site">
        <div class="container site-inner">
            <a href="{{ route('public.dashboard') }}" class="brand">
                <img src="{{ asset('images/logo.png') }}" alt="">
                <div class="brand-text">
                    <div class="name">TISU Marketing</div>
                    <div class="tag">To'lov statistikasi</div>
                </div>
            </a>
            @isset($report)
                <div class="header-meta">
                    <div class="report-tag">
                        <span class="dot"></span>
                        <span>{{ $report->report_date->format('d.m.Y') }}</span>
                    </div>
                </div>
            @endisset
        </div>
    </header>

    <main>
        <div class="container">
            @yield('content')
        </div>
    </main>
</body>
</html>
