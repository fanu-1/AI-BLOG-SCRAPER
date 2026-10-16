@extends('layouts.admin')

@section('title', 'Scraped Items')

@section('content')
<div class="topbar" style="margin-bottom:16px;">
    <div>
        <h1 style="margin:0;">Scraped Items</h1>
        <div class="muted">Raw content stored from sources, before any AI processing.</div>
    </div>
    <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Back</a>
</div>

<div class="table-box panel">
    <table>
        <thead>
            <tr><th>Title</th><th>Source</th><th>URL</th><th>Fetched</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->title ?: 'Untitled' }}</strong>
                        <div class="small">{{ $item->excerpt }}</div>
                    </td>
                    <td>{{ $item->source?->name ?? '—' }}</td>
                    <td><a class="link" href="{{ $item->url }}" target="_blank" rel="noreferrer">{{ $item->url ?: '—' }}</a></td>
                    <td>{{ optional($item->fetched_at)->format('M d, Y H:i') ?? '—' }}</td>
                    <td><span class="pill">{{ $item->http_status ?: 'n/a' }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination">{{ $items->links() }}</div>
</div>
@endsection
