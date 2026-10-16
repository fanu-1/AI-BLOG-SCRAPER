@extends('layouts.admin')

@section('title', 'Articles')

@section('content')
<div class="topbar" style="margin-bottom:16px;">
    <div>
        <h1 style="margin:0;">Articles</h1>
        <div class="muted">Review, publish, and edit generated posts.</div>
    </div>
    <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
</div>

<div class="table-box panel">
    <table>
        <thead>
            <tr><th>Title</th><th>Category</th><th>Status</th><th>Published</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @foreach ($articles as $article)
                <tr>
                    <td>{{ $article->title }}</td>
                    <td>{{ $article->category?->name ?? '—' }}</td>
                    <td><span class="pill">{{ $article->status }}</span></td>
                    <td>{{ optional($article->published_at)->format('M d, Y H:i') ?? '—' }}</td>
                    <td class="actions">
                        <a class="btn btn-secondary btn-small" href="{{ route('admin.articles.edit', $article) }}">Edit</a>
                        @if ($article->status !== 'published')
                            <form method="POST" action="{{ route('admin.articles.publish', $article) }}">@csrf<button class="btn btn-primary btn-small" type="submit">Publish</button></form>
                        @else
                            <form method="POST" action="{{ route('admin.articles.unpublish', $article) }}">@csrf<button class="btn btn-secondary btn-small" type="submit">Unpublish</button></form>
                        @endif
                        <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Delete article?')">@csrf @method('DELETE')<button class="btn btn-danger btn-small" type="submit">Delete</button></form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination">{{ $articles->links() }}</div>
</div>
@endsection
