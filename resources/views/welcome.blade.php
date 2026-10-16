@extends('layouts.site')

@section('title', 'BlogAI Scraper')
@section('meta_description', 'A premium AI-driven blog and news publishing platform.')

@section('content')
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <div class="eyebrow">Premium editorial automation</div>
            <h1>Scrape stories. Rewrite with AI. Publish beautifully.</h1>
            <p>
                BlogAI Scraper helps you manage news sources, AI providers, editorial categories, and daily content
                automation from one polished admin workspace.
            </p>
            <div class="actions" style="margin-top:24px;">
                <a class="btn btn-primary" href="{{ route('login') }}">Open Admin Panel</a>
                <a class="btn btn-secondary" href="#featured">Explore Articles</a>
            </div>
        </div>

        <div class="hero-card">
            <div class="eyebrow" style="margin-bottom:14px;">Workflow</div>
            <div class="feature-grid">
                <div class="card">
                    <h3>1. Scrape</h3>
                    <p>RSS and HTML sources pull fresh content on schedule.</p>
                </div>
                <div class="card">
                    <h3>2. Generate</h3>
                    <p>OpenAI, Gemini, or Claude rewrites the source into unique content.</p>
                </div>
                <div class="card">
                    <h3>3. Publish</h3>
                    <p>Approve drafts or auto-publish directly by source rules.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container stats">
        <div class="stat"><strong>{{ $categories->count() }}</strong><div class="muted">Categories</div></div>
        <div class="stat"><strong>{{ $featuredArticles->count() }}</strong><div class="muted">Featured stories</div></div>
        <div class="stat"><strong>{{ $latestArticles->total() }}</strong><div class="muted">Published articles</div></div>
        <div class="stat"><strong>Daily</strong><div class="muted">Auto sync ready</div></div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <div class="eyebrow">Categories</div>
                <h2>Browse editorial sections</h2>
            </div>
        </div>
        <div class="grid">
            @foreach ($categories as $category)
                <a class="card" href="{{ route('categories.show', $category) }}">
                    <div class="tag">{{ $category->name }}</div>
                    <h3 style="margin-top:14px;">{{ $category->name }}</h3>
                    <p>{{ $category->description }}</p>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="section" id="featured">
    <div class="container content-grid">
        <div>
            <div class="section-head">
                <div>
                    <div class="eyebrow">Featured</div>
                    <h2>Top stories</h2>
                </div>
            </div>

            <div class="grid" style="grid-template-columns:1fr;">
                @forelse ($featuredArticles as $article)
                    <article class="article">
                        <div class="meta-row">
                            @if ($article->category)
                                <a class="tag" href="{{ route('categories.show', $article->category) }}">{{ $article->category->name }}</a>
                            @endif
                            <span class="small">{{ optional($article->published_at)->format('M d, Y') }}</span>
                        </div>
                        <h2><a href="{{ route('articles.show', $article) }}">{{ $article->title }}</a></h2>
                        <p class="excerpt">{{ $article->excerpt }}</p>
                        <a class="btn btn-secondary" href="{{ route('articles.show', $article) }}">Read story</a>
                    </article>
                @empty
                    <div class="panel">No published articles yet. Run a sync from the admin panel.</div>
                @endforelse
            </div>
        </div>

        <aside class="sidebar">
            <div class="panel newsletter">
                <div class="eyebrow">Why this works</div>
                <h3 style="margin-top:12px;">A professional content pipeline</h3>
                <p class="muted">You control sources, AI model, categories, and publishing behavior without touching code each time.</p>
            </div>
            <div class="panel">
                <div class="eyebrow">AI providers</div>
                <h3 style="margin-top:12px;">OpenAI, Gemini, Claude</h3>
                <p class="muted">Add your key once in admin and assign that provider to any source.</p>
            </div>
        </aside>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <div class="eyebrow">Latest</div>
                <h2>Recent articles</h2>
            </div>
        </div>
        <div class="grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
            @foreach ($latestArticles as $article)
                <article class="article">
                    <div class="meta-row">
                        @if ($article->category)
                            <a class="tag" href="{{ route('categories.show', $article->category) }}">{{ $article->category->name }}</a>
                        @endif
                        <span class="small">{{ optional($article->published_at)->format('M d, Y') }}</span>
                    </div>
                    <h2><a href="{{ route('articles.show', $article) }}">{{ $article->title }}</a></h2>
                    <p class="excerpt">{{ $article->excerpt }}</p>
                </article>
            @endforeach
        </div>
        <div class="pagination">{{ $latestArticles->links() }}</div>
    </div>
</section>
@endsection
