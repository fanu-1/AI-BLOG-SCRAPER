<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') - {{ config('app.name', 'Blog AI Scraper') }}</title>
    <style>
        :root {
            --bg: #081120;
            --panel: #0f172a;
            --card: #111827;
            --muted: #94a3b8;
            --text: #e5eefc;
            --accent: #8b5cf6;
            --border: rgba(148, 163, 184, 0.18);
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Inter, ui-sans-serif, system-ui, sans-serif; background: linear-gradient(180deg, #050816, var(--bg)); color: var(--text); }
        a { color: inherit; text-decoration: none; }
        .container { width: min(1440px, calc(100% - 32px)); margin: 0 auto; }
        .shell { display: grid; grid-template-columns: 250px 1fr; min-height: 100vh; }
        .sidebar { border-right: 1px solid var(--border); background: rgba(15, 23, 42, .92); padding: 24px; position: sticky; top: 0; height: 100vh; }
        .main { padding: 24px; }
        .brand { font-weight: 800; font-size: 1.2rem; margin-bottom: 24px; display: block; }
        .nav a, .link {
            display: block; padding: 12px 14px; border-radius: 12px; margin-bottom: 8px; color: #dbeafe;
            border: 1px solid transparent;
        }
        .nav a:hover, .nav a.active, .link:hover { background: rgba(139, 92, 246, .12); border-color: rgba(139, 92, 246, .2); }
        .panel, .card, .table-box { background: rgba(15, 23, 42, .84); border: 1px solid var(--border); border-radius: 18px; padding: 20px; }
        .grid { display: grid; gap: 16px; }
        .stats { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 16px; }
        .stat strong { display: block; font-size: 1.5rem; margin-bottom: 6px; }
        .muted { color: var(--muted); }
        .topbar, .section-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 16px; border-radius: 999px; border: 1px solid transparent; font-weight: 700; cursor: pointer; }
        .btn-primary { background: linear-gradient(135deg, var(--accent), #2563eb); color: white; }
        .btn-secondary { background: transparent; color: var(--text); border-color: var(--border); }
        .btn-danger { background: rgba(239, 68, 68, .14); color: #fecaca; border-color: rgba(248, 113, 113, .22); }
        .btn-small { padding: 8px 12px; font-size: .9rem; }
        .alert { padding: 14px 16px; border-radius: 14px; background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.18); margin-bottom: 16px; }
        label { display: grid; gap: 8px; margin-bottom: 14px; }
        input, textarea, select {
            width: 100%; padding: 11px 13px; border-radius: 12px; border: 1px solid var(--border);
            background: rgba(2, 6, 23, .9); color: var(--text);
        }
        textarea { min-height: 110px; resize: vertical; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .table-box { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 12px 10px; border-bottom: 1px solid var(--border); vertical-align: top; }
        th { color: #cbd5e1; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .pill { padding: 6px 10px; border-radius: 999px; background: rgba(139,92,246,.13); border: 1px solid rgba(139,92,246,.2); }
        .full-width { grid-column: 1 / -1; }
        details summary { cursor: pointer; color: #c4b5fd; margin-bottom: 10px; }
        @media (max-width: 1100px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { position: relative; height: auto; }
            .stats, .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('admin.dashboard') }}">BlogAI Scraper</a>
        <nav class="nav">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.articles.index') }}">Articles</a>
            <a href="{{ route('admin.categories.index') }}">Categories</a>
            <a href="{{ route('admin.sources.index') }}">Sources</a>
            <a href="{{ route('admin.scraped-items.index') }}">Scraped Items</a>
            <a href="{{ route('admin.providers.index') }}">AI Providers</a>
            <a href="{{ route('home') }}">View Site</a>
        </nav>
        <form method="POST" action="{{ route('logout') }}" style="margin-top: 18px;">
            @csrf
            <button class="btn btn-secondary" type="submit">Logout</button>
        </form>
    </aside>
    <main class="main">
        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
</div>
</body>
</html>
