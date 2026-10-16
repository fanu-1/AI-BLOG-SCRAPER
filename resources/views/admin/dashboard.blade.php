@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="topbar" style="margin-bottom:16px;">
    <div>
        <h1 style="margin:0;">Dashboard</h1>
        <div class="muted">Overview of your publishing engine and automation status.</div>
    </div>
    <div class="actions">
        <form method="POST" action="{{ route('admin.sync') }}">
            @csrf
            <button class="btn btn-primary" type="submit">Run Sync</button>
        </form>
        <a class="btn btn-secondary" href="{{ route('admin.articles.index') }}">Open Articles</a>
    </div>
</div>

<div class="stats" style="margin-bottom:20px;">
    @foreach ($stats as $label => $value)
        <div class="panel">
            <strong>{{ $value }}</strong>
            <div class="muted">{{ str_replace('_', ' ', ucfirst($label)) }}</div>
        </div>
    @endforeach
</div>

<div class="grid">
    <section class="panel">
        <div class="section-head">
            <div>
                <h2 style="margin:0;">Quick Actions</h2>
                <div class="muted">Use dedicated screens for clean content management.</div>
            </div>
        </div>
        <div class="actions" style="margin-top:16px;">
            <a class="btn btn-secondary" href="{{ route('admin.categories.index') }}">Manage Categories</a>
            <a class="btn btn-secondary" href="{{ route('admin.providers.index') }}">Manage AI Providers</a>
            <a class="btn btn-secondary" href="{{ route('admin.sources.index') }}">Manage Sources</a>
            <a class="btn btn-secondary" href="{{ route('admin.articles.index') }}">Manage Articles</a>
        </div>
    </section>

    <section class="panel">
        <div class="section-head">
            <div>
                <h2 style="margin:0;">Recent Articles</h2>
                <div class="muted">Latest generated posts.</div>
            </div>
            <a class="btn btn-secondary btn-small" href="{{ route('admin.articles.index') }}">View all</a>
        </div>
        <div class="table-box" style="margin-top:14px;">
            <table>
                <thead>
                    <tr><th>Title</th><th>Category</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @foreach ($recentArticles as $article)
                        <tr>
                            <td>{{ $article->title }}</td>
                            <td>{{ $article->category?->name ?? '—' }}</td>
                            <td><span class="pill">{{ $article->status }}</span></td>
                            <td><a class="link" href="{{ route('admin.articles.edit', $article) }}">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="section-head">
            <div>
                <h2 style="margin:0;">Recent Sync Runs</h2>
                <div class="muted">Track scraping and generation status.</div>
            </div>
        </div>
        <div class="table-box" style="margin-top:14px;">
            <table>
                <thead>
                    <tr><th>Source</th><th>Status</th><th>Items</th><th>Created</th><th>Finished</th></tr>
                </thead>
                <tbody>
                    @foreach ($recentRuns as $run)
                        <tr>
                            <td>{{ $run->source?->name ?? 'Manual' }}</td>
                            <td><span class="pill">{{ $run->status }}</span></td>
                            <td>{{ $run->items_found }}</td>
                            <td>{{ $run->articles_created }}</td>
                            <td>{{ optional($run->finished_at)->format('M d, Y H:i') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
