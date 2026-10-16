@extends('layouts.admin')

@section('title', 'Sources')

@section('content')
<div class="topbar" style="margin-bottom:16px;">
    <div>
        <h1 style="margin:0;">Sources</h1>
        <div class="muted">Manage RSS feeds and HTML sources for your scraper.</div>
    </div>
    <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Back</a>
</div>

<div class="panel" style="margin-bottom:20px;">
    <h2 style="margin-top:0;">Add Source</h2>
    <form method="POST" action="{{ route('admin.sources.store') }}" class="form-grid">
        @csrf
        <label>Name <input name="name" required placeholder="TechCrunch RSS"></label>
        <label>Source type
            <select name="source_type">
                <option value="rss">RSS</option>
                <option value="html">HTML</option>
            </select>
        </label>
        <label>Crawl mode
            <select name="crawl_mode">
                <option value="auto">Auto</option>
                <option value="rss">RSS</option>
                <option value="sitemap">Sitemap</option>
                <option value="pagination">Pagination</option>
                <option value="html">HTML</option>
            </select>
        </label>
        <label>Source URL <input name="source_url" required placeholder="https://..."></label>
        <label>Feed URL <input name="feed_url" placeholder="Optional for HTML pages"></label>
        <label>Sitemap URL <input name="sitemap_url" placeholder="https://site.com/sitemap.xml"></label>
        <label>Category
            <select name="category_id">
                <option value="">None</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </label>
        <label>AI Provider
            <select name="ai_provider_id">
                <option value="">Auto pick</option>
                @foreach ($providers as $provider)
                    <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                @endforeach
            </select>
        </label>
        <label>Language <input name="language" value="en"></label>
        <label>Max items <input type="number" name="max_items" value="10"></label>
        <label>Max depth <input type="number" name="max_depth" value="3"></label>
        <label>Max pages <input type="number" name="max_pages" value="50"></label>
        <label><span>Auto publish</span><input type="checkbox" name="auto_publish" value="1"></label>
        <label><span>Active</span><input type="checkbox" name="is_active" value="1" checked></label>
        <label class="full-width">Description <textarea name="description" placeholder="Optional note"></textarea></label>
        <details class="full-width">
            <summary>Advanced XPath selectors</summary>
            <div class="form-grid" style="margin-top:12px;">
                <label>Title XPath <input name="title_xpath" placeholder="//title"></label>
                <label>Content XPath <input name="content_xpath" placeholder="//article//p"></label>
                <label>Excerpt XPath <input name="excerpt_xpath"></label>
                <label>Link XPath <input name="link_xpath"></label>
                <label>Image XPath <input name="image_xpath"></label>
                <label>Item XPath <input name="item_xpath"></label>
                <label>Next Page XPath <input name="next_page_xpath" placeholder='//a[@rel="next"]/@href'></label>
                <label class="full-width">Rules JSON <textarea name="rules_json" placeholder='{"article_link_xpath":"//article//a/@href"}'></textarea></label>
            </div>
        </details>
        <div class="full-width"><button class="btn btn-primary" type="submit">Create Source</button></div>
    </form>
</div>

<div class="table-box panel">
    <table>
        <thead>
            <tr><th>Name</th><th>Type</th><th>Category</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @foreach ($sources as $source)
                <tr>
                    <td colspan="5">
                        <form id="source-update-{{ $source->id }}" method="POST" action="{{ route('admin.sources.update', $source) }}" class="form-grid">
                            @csrf
                            @method('PATCH')
                            <label>Name <input name="name" value="{{ $source->name }}"></label>
                            <label>Source type
                                <select name="source_type">
                                    <option value="rss" @selected($source->source_type === 'rss')>RSS</option>
                                    <option value="html" @selected($source->source_type === 'html')>HTML</option>
                                </select>
                            </label>
                            <label>Crawl mode
                                <select name="crawl_mode">
                                    <option value="auto" @selected($source->crawl_mode === 'auto')>Auto</option>
                                    <option value="rss" @selected($source->crawl_mode === 'rss')>RSS</option>
                                    <option value="sitemap" @selected($source->crawl_mode === 'sitemap')>Sitemap</option>
                                    <option value="pagination" @selected($source->crawl_mode === 'pagination')>Pagination</option>
                                    <option value="html" @selected($source->crawl_mode === 'html')>HTML</option>
                                </select>
                            </label>
                            <label>Source URL <input name="source_url" value="{{ $source->source_url }}"></label>
                            <label>Feed URL <input name="feed_url" value="{{ $source->feed_url }}"></label>
                            <label>Sitemap URL <input name="sitemap_url" value="{{ $source->sitemap_url }}"></label>
                            <label>Category
                                <select name="category_id">
                                    <option value="">None</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected($source->category_id === $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>AI Provider
                                <select name="ai_provider_id">
                                    <option value="">Auto pick</option>
                                    @foreach ($providers as $provider)
                                        <option value="{{ $provider->id }}" @selected($source->ai_provider_id === $provider->id)>{{ $provider->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>Language <input name="language" value="{{ $source->language }}"></label>
                            <label>Max items <input type="number" name="max_items" value="{{ $source->max_items }}"></label>
                            <label>Max depth <input type="number" name="max_depth" value="{{ $source->max_depth }}"></label>
                            <label>Max pages <input type="number" name="max_pages" value="{{ $source->max_pages }}"></label>
                            <label><span>Auto publish</span><input type="checkbox" name="auto_publish" value="1" @checked($source->auto_publish)></label>
                            <label><span>Active</span><input type="checkbox" name="is_active" value="1" @checked($source->is_active)></label>
                            <label class="full-width">Description <textarea name="description">{{ $source->description }}</textarea></label>
                            <details class="full-width">
                                <summary>Advanced XPath selectors</summary>
                                <div class="form-grid" style="margin-top:12px;">
                                    <label>Title XPath <input name="title_xpath" value="{{ $source->title_xpath }}"></label>
                                    <label>Content XPath <input name="content_xpath" value="{{ $source->content_xpath }}"></label>
                                    <label>Excerpt XPath <input name="excerpt_xpath" value="{{ $source->excerpt_xpath }}"></label>
                                    <label>Link XPath <input name="link_xpath" value="{{ $source->link_xpath }}"></label>
                                    <label>Image XPath <input name="image_xpath" value="{{ $source->image_xpath }}"></label>
                                    <label>Item XPath <input name="item_xpath" value="{{ $source->item_xpath }}"></label>
                                    <label>Next Page XPath <input name="next_page_xpath" value="{{ $source->next_page_xpath }}"></label>
                                    <label class="full-width">Rules JSON <textarea name="rules_json">{{ is_array($source->rules_json) ? json_encode($source->rules_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '' }}</textarea></label>
                                </div>
                            </details>
                        </form>
                        <div class="actions" style="margin-top:12px;">
                            <button class="btn btn-primary btn-small" type="submit" form="source-update-{{ $source->id }}">Save</button>
                            <form id="source-sync-{{ $source->id }}" method="POST" action="{{ route('admin.sources.sync', $source) }}">
                                @csrf
                            </form>
                            <button class="btn btn-secondary btn-small" type="submit" form="source-sync-{{ $source->id }}">Sync</button>
                            <form id="source-delete-{{ $source->id }}" method="POST" action="{{ route('admin.sources.destroy', $source) }}" onsubmit="return confirm('Delete source?')">
                                @csrf
                                @method('DELETE')
                            </form>
                            <button class="btn btn-danger btn-small" type="submit" form="source-delete-{{ $source->id }}">Delete</button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
