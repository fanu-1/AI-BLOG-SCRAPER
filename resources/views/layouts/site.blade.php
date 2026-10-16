<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'BlogAI Scraper'))</title>
    <meta name="description" content="@yield('meta_description', 'AI-powered blog and news publishing platform.')">
    <style>
        :root {
            --bg: #07111f;
            --panel: rgba(10, 18, 35, 0.72);
            --card: rgba(13, 22, 42, 0.9);
            --muted: #94a3b8;
            --text: #e2e8f0;
            --accent: #8b5cf6;
            --accent-2: #06b6d4;
            --border: rgba(148, 163, 184, 0.14);
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(139, 92, 246, 0.22), transparent 28%),
                radial-gradient(circle at top right, rgba(6, 182, 212, 0.18), transparent 25%),
                linear-gradient(180deg, #050816, var(--bg));
        }
        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }
        .container { width: min(1200px, calc(100% - 32px)); margin: 0 auto; }
        .bar, .panel, .card, .article, .pill, .stat, .hero-card {
            border: 1px solid var(--border);
            background: var(--panel);
            backdrop-filter: blur(16px);
            border-radius: 22px;
            box-shadow: 0 24px 60px rgba(2, 6, 23, 0.35);
        }
        .nav { padding: 22px 0 8px; position: sticky; top: 0; z-index: 40; }
        .nav-inner, .footer-inner, .hero-grid, .grid, .stats, .feature-grid, .content-grid {
            display: grid;
            gap: 20px;
        }
        .nav-inner {
            grid-template-columns: 1fr auto;
            align-items: center;
            padding: 16px 20px;
            background: rgba(3, 7, 18, 0.55);
        }
        .brand { font-size: 1.12rem; font-weight: 800; letter-spacing: .02em; }
        .brand span { color: #c4b5fd; }
        .nav-links, .pill-row, .meta-row, .actions {
            display: flex; gap: 12px; flex-wrap: wrap; align-items: center;
        }
        .pill {
            padding: 8px 12px;
            border-radius: 999px;
            color: #dbeafe;
            font-size: .9rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 12px 18px;
            border: 1px solid transparent;
            font-weight: 700;
            cursor: pointer;
            transition: transform .18s ease, border-color .18s ease, background .18s ease;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: linear-gradient(135deg, var(--accent), #2563eb); color: white; }
        .btn-secondary { background: rgba(15, 23, 42, .65); color: var(--text); border-color: var(--border); }
        .btn-danger { background: rgba(220, 38, 38, .16); color: #fecaca; border-color: rgba(248, 113, 113, .2); }
        .hero { padding: 42px 0 24px; }
        .hero-grid { grid-template-columns: 1.15fr .85fr; align-items: center; }
        .hero-copy h1 {
            font-size: clamp(2.4rem, 6vw, 5rem);
            line-height: .92;
            margin: 0 0 18px;
            letter-spacing: -.04em;
        }
        .hero-copy p { color: var(--muted); font-size: 1.05rem; line-height: 1.75; max-width: 62ch; }
        .hero-card, .panel, .card, .article, .stat { padding: 22px; }
        .eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 12px; border-radius: 999px;
            background: rgba(139, 92, 246, .12); border: 1px solid rgba(139, 92, 246, .18);
            color: #ddd6fe; font-size: .85rem; text-transform: uppercase; letter-spacing: .1em;
        }
        .stats { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .stat strong, .card h3 { font-size: 1.55rem; display: block; margin-bottom: 8px; }
        .section { padding: 26px 0; }
        .section-head { display:flex; align-items:end; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom: 16px; }
        .section-head h2 { margin: 0; font-size: 1.7rem; }
        .muted, .card p, .article .excerpt, .small { color: var(--muted); }
        .grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .feature-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .content-grid { grid-template-columns: 1.4fr .6fr; align-items:start; }
        .card:hover, .article:hover { border-color: rgba(139, 92, 246, .25); transform: translateY(-2px); }
        .card, .article { transition: transform .18s ease, border-color .18s ease; }
        .article { display: grid; gap: 14px; }
        .article h2, .article h1 { margin: 0; line-height: 1.15; letter-spacing: -.02em; }
        .article-content { line-height: 1.9; color: #dbeafe; }
        .article-content h2, .article-content h3 { margin-top: 1.8em; }
        .article-content p { margin: 1em 0; }
        .article-content img { max-width: 100%; border-radius: 18px; }
        .tag { padding: 6px 10px; border-radius: 999px; border: 1px solid var(--border); color: #dbeafe; }
        .sidebar { display: grid; gap: 16px; }
        .newsletter {
            background: linear-gradient(135deg, rgba(139,92,246,.18), rgba(6,182,212,.12));
            border-color: rgba(139, 92, 246, .22);
        }
        .pagination { display:flex; gap:8px; flex-wrap:wrap; margin-top: 20px; }
        .pagination a, .pagination span { padding: 8px 12px; border-radius: 10px; border: 1px solid var(--border); background: rgba(15, 23, 42, .65); }
        .footer { color: var(--muted); padding: 32px 0 44px; }
        .footer-inner { grid-template-columns: 1fr auto; align-items: center; }
        @media (max-width: 1024px) {
            .hero-grid, .stats, .grid, .feature-grid, .content-grid, .footer-inner { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header class="nav">
        <div class="container">
            <div class="bar nav-inner">
                <a class="brand" href="{{ route('home') }}">Blog<span>AI</span>Scraper</a>
                <nav class="nav-links">
                    <a class="pill" href="{{ route('home') }}">Home</a>
                    @auth
                        <a class="pill" href="{{ route('admin.dashboard') }}">Admin</a>
                    @else
                        <a class="pill" href="{{ route('login') }}">Admin Login</a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container footer-inner">
            <div>
                <strong>BlogAI Scraper</strong>
                <div class="small">Premium AI-powered content publishing platform.</div>
            </div>
            <div class="small">Automated scraping • AI rewriting • Editorial control</div>
        </div>
    </footer>
</body>
</html>
