@extends('layouts.site')

@section('title', $article->seo_title ?: $article->title)
@section('meta_description', $article->seo_description ?: $article->excerpt)

@section('content')
<section class="section">
    <div class="container content-grid">
        <article class="article panel">
            <div class="meta-row">
                @if ($article->category)
                    <a class="tag" href="{{ route('categories.show', $article->category) }}">{{ $article->category->name }}</a>
                @endif
                <span class="small">{{ optional($article->published_at)->format('M d, Y') ?? 'Draft' }}</span>
                <span class="small">Source: {{ $article->source?->name ?? 'Editorial' }}</span>
            </div>
            <h1>{{ $article->title }}</h1>
            <p class="excerpt">{{ $article->excerpt }}</p>

            @if ($article->featured_image_url)
                <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" style="border-radius:18px; border:1px solid rgba(148,163,184,.15);">
            @endif

            <div class="article-content">
                {!! $article->body_html !!}
            </div>
        </article>

        <aside class="sidebar">
            <div class="panel">
                <div class="eyebrow">Article info</div>
                <h3 style="margin-top:12px;">{{ $article->title }}</h3>
                <div class="small">AI provider: {{ $article->provider?->name ?? 'N/A' }}</div>
                <div class="small">Published: {{ optional($article->published_at)->format('M d, Y h:i A') ?? 'Draft' }}</div>
            </div>

            <div class="panel">
                <div class="eyebrow">Related</div>
                <div class="sidebar" style="margin-top:14px;">
                    @forelse ($relatedArticles as $related)
                        <a class="link" href="{{ route('articles.show', $related) }}">
                            <strong>{{ $related->title }}</strong>
                            <div class="small">{{ $related->excerpt }}</div>
                        </a>
                    @empty
                        <div class="small">No related articles yet.</div>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</section>
@endsection
