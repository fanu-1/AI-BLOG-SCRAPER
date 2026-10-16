@extends('layouts.admin')

@section('title', 'Edit Article')

@section('content')
<div class="topbar" style="margin-bottom:16px;">
    <div>
        <h1 style="margin:0;">Edit Article</h1>
        <div class="muted">{{ $article->title }}</div>
    </div>
    <div class="actions">
        <a class="btn btn-secondary" href="{{ route('admin.articles.index') }}">Back to Articles</a>
        <a class="btn btn-secondary" href="{{ route('articles.show', $article) }}">View Public Page</a>
    </div>
</div>

<div class="panel">
    <form id="article-update" method="POST" action="{{ route('admin.articles.update', $article) }}" class="form-grid">
        @csrf
        @method('PATCH')
        <label>Title <input name="title" value="{{ $article->title }}" required></label>
        <label>Slug <input name="slug" value="{{ $article->slug }}" required></label>
        <label>Status
            <select name="status">
                <option value="draft" @selected($article->status === 'draft')>Draft</option>
                <option value="published" @selected($article->status === 'published')>Published</option>
                <option value="scheduled" @selected($article->status === 'scheduled')>Scheduled</option>
            </select>
        </label>
        <label><span>Featured</span><input type="checkbox" name="is_featured" value="1" @checked($article->is_featured)></label>
        <label class="full-width">Excerpt <textarea name="excerpt">{{ $article->excerpt }}</textarea></label>
        <label class="full-width">Body HTML <textarea name="body_html" style="min-height:300px;">{{ $article->body_html }}</textarea></label>
        <label>SEO Title <input name="seo_title" value="{{ $article->seo_title }}"></label>
        <label>SEO Description <input name="seo_description" value="{{ $article->seo_description }}"></label>
    </form>
    <div class="actions" style="margin-top:14px;">
        <button class="btn btn-primary" type="submit" form="article-update">Save Changes</button>
        <form id="article-publish" method="POST" action="{{ route('admin.articles.publish', $article) }}">@csrf</form>
        <form id="article-unpublish" method="POST" action="{{ route('admin.articles.unpublish', $article) }}">@csrf</form>
        @if ($article->status !== 'published')
            <button class="btn btn-secondary" type="submit" form="article-publish">Publish</button>
        @else
            <button class="btn btn-secondary" type="submit" form="article-unpublish">Unpublish</button>
        @endif
    </div>
</div>
@endsection
