@extends('layouts.site')

@section('title', $category->name)
@section('meta_description', $category->description ?: $category->name)

@section('content')
<section class="section">
    <div class="container">
        <div class="panel">
            <div class="eyebrow">Category</div>
            <h1 style="margin:14px 0 10px;">{{ $category->name }}</h1>
            <p class="muted" style="max-width: 65ch;">{{ $category->description }}</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
        @forelse ($articles as $article)
            <article class="article">
                <div class="meta-row">
                    <span class="tag">{{ $category->name }}</span>
                    <span class="small">{{ optional($article->published_at)->format('M d, Y') }}</span>
                </div>
                <h2><a href="{{ route('articles.show', $article) }}">{{ $article->title }}</a></h2>
                <p class="excerpt">{{ $article->excerpt }}</p>
                <a class="btn btn-secondary" href="{{ route('articles.show', $article) }}">Read more</a>
            </article>
        @empty
            <div class="panel">No published posts in this category yet.</div>
        @endforelse
    </div>
    <div class="container">
        <div class="pagination">{{ $articles->links() }}</div>
    </div>
</section>
@endsection
