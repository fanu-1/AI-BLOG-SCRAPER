<?php

namespace App\Http\Controllers\Admin;

use App\Models\Article;
use App\Models\AiProvider;
use App\Models\Category;
use App\Models\ContentSource;
use App\Models\ScrapeRun;
use App\Services\ContentSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DashboardController extends BaseAdminController
{
    public function index(): View
    {
        $this->admin();

        return view('admin.dashboard', [
            'stats' => [
                'articles' => Article::count(),
                'published' => Article::published()->count(),
                'categories' => Category::count(),
                'sources' => ContentSource::count(),
                'providers' => AiProvider::count(),
                'runs' => ScrapeRun::count(),
            ],
            'categories' => Category::query()->orderBy('sort_order')->get(),
            'providers' => AiProvider::query()->latest()->get(),
            'sources' => ContentSource::query()->with(['category', 'provider'])->latest()->get(),
            'recentArticles' => Article::query()->with(['category', 'source'])->latest()->take(10)->get(),
            'recentRuns' => ScrapeRun::query()->with('source')->latest()->take(10)->get(),
        ]);
    }

    public function categories(): View
    {
        $this->admin();

        return view('admin.categories.index', [
            'categories' => Category::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function providers(): View
    {
        $this->admin();

        return view('admin.providers.index', [
            'providers' => AiProvider::query()->latest()->get(),
        ]);
    }

    public function sources(): View
    {
        $this->admin();

        return view('admin.sources.index', [
            'sources' => ContentSource::query()->with(['category', 'provider'])->latest()->get(),
            'categories' => Category::query()->orderBy('sort_order')->get(),
            'providers' => AiProvider::query()->latest()->get(),
        ]);
    }

    public function syncAll(ContentSyncService $syncService): RedirectResponse
    {
        $this->admin();

        $queued = $syncService->queueAll();

        return back()->with('status', "Queued {$queued} source crawl job(s).");
    }

    public function syncSource(ContentSource $source, ContentSyncService $syncService): RedirectResponse
    {
        $this->admin();

        $syncService->queueSource($source);

        return back()->with('status', "Queued crawl for {$source->name}.");
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $this->admin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return back()->with('status', 'Category created.');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $this->admin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return back()->with('status', 'Category updated.');
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        $this->admin();
        $category->delete();

        return back()->with('status', 'Category deleted.');
    }

    public function storeProvider(Request $request): RedirectResponse
    {
        $this->admin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'driver' => ['required', 'in:openai,gemini,claude'],
            'base_url' => ['nullable', 'string', 'max:255'],
            'api_key' => ['nullable', 'string'],
            'model' => ['required', 'string', 'max:255'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'max_tokens' => ['nullable', 'integer', 'min:1'],
            'system_prompt' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        AiProvider::create([
            ...$data,
            'api_key' => $data['api_key'] ?? null,
            'temperature' => $data['temperature'] ?? 0.7,
            'max_tokens' => $data['max_tokens'] ?? 2000,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'AI provider created.');
    }

    public function updateProvider(Request $request, AiProvider $provider): RedirectResponse
    {
        $this->admin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'driver' => ['required', 'in:openai,gemini,claude'],
            'base_url' => ['nullable', 'string', 'max:255'],
            'api_key' => ['nullable', 'string'],
            'model' => ['required', 'string', 'max:255'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'max_tokens' => ['nullable', 'integer', 'min:1'],
            'system_prompt' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $provider->update([
            ...$data,
            'api_key' => array_key_exists('api_key', $data) && $data['api_key'] !== '' ? $data['api_key'] : $provider->getRawOriginal('api_key'),
            'temperature' => $data['temperature'] ?? $provider->temperature,
            'max_tokens' => $data['max_tokens'] ?? $provider->max_tokens,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return back()->with('status', 'AI provider updated.');
    }

    public function destroyProvider(AiProvider $provider): RedirectResponse
    {
        $this->admin();
        $provider->delete();

        return back()->with('status', 'AI provider deleted.');
    }

    public function storeSource(Request $request): RedirectResponse
    {
        $this->admin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'source_type' => ['required', 'in:html,rss'],
            'crawl_mode' => ['required', 'in:auto,rss,sitemap,pagination,html'],
            'source_url' => ['required', 'url'],
            'feed_url' => ['nullable', 'url'],
            'sitemap_url' => ['nullable', 'url'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'ai_provider_id' => ['nullable', 'exists:ai_providers,id'],
            'language' => ['nullable', 'string', 'max:10'],
            'rules_json' => ['nullable', 'string'],
            'title_xpath' => ['nullable', 'string'],
            'content_xpath' => ['nullable', 'string'],
            'excerpt_xpath' => ['nullable', 'string'],
            'link_xpath' => ['nullable', 'string'],
            'image_xpath' => ['nullable', 'string'],
            'item_xpath' => ['nullable', 'string'],
            'next_page_xpath' => ['nullable', 'string'],
            'max_depth' => ['nullable', 'integer', 'min:1'],
            'max_pages' => ['nullable', 'integer', 'min:1'],
            'auto_publish' => ['nullable', 'boolean'],
            'max_items' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ContentSource::create([
            ...$data,
            'slug' => Str::slug($data['name']),
            'language' => $data['language'] ?? 'en',
            'rules_json' => $this->decodeRulesJson($data['rules_json'] ?? null),
            'auto_publish' => $request->boolean('auto_publish', false),
            'max_items' => $data['max_items'] ?? 10,
            'max_depth' => $data['max_depth'] ?? 3,
            'max_pages' => $data['max_pages'] ?? 50,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Source created.');
    }

    public function updateSource(Request $request, ContentSource $source): RedirectResponse
    {
        $this->admin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'source_type' => ['required', 'in:html,rss'],
            'crawl_mode' => ['required', 'in:auto,rss,sitemap,pagination,html'],
            'source_url' => ['required', 'url'],
            'feed_url' => ['nullable', 'url'],
            'sitemap_url' => ['nullable', 'url'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'ai_provider_id' => ['nullable', 'exists:ai_providers,id'],
            'language' => ['nullable', 'string', 'max:10'],
            'rules_json' => ['nullable', 'string'],
            'title_xpath' => ['nullable', 'string'],
            'content_xpath' => ['nullable', 'string'],
            'excerpt_xpath' => ['nullable', 'string'],
            'link_xpath' => ['nullable', 'string'],
            'image_xpath' => ['nullable', 'string'],
            'item_xpath' => ['nullable', 'string'],
            'next_page_xpath' => ['nullable', 'string'],
            'max_depth' => ['nullable', 'integer', 'min:1'],
            'max_pages' => ['nullable', 'integer', 'min:1'],
            'auto_publish' => ['nullable', 'boolean'],
            'max_items' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $source->update([
            ...$data,
            'slug' => Str::slug($data['name']),
            'language' => $data['language'] ?? 'en',
            'rules_json' => $this->decodeRulesJson($data['rules_json'] ?? null),
            'auto_publish' => $request->boolean('auto_publish', false),
            'max_items' => $data['max_items'] ?? 10,
            'max_depth' => $data['max_depth'] ?? 3,
            'max_pages' => $data['max_pages'] ?? 50,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return back()->with('status', 'Source updated.');
    }

    public function destroySource(ContentSource $source): RedirectResponse
    {
        $this->admin();
        $source->delete();

        return back()->with('status', 'Source deleted.');
    }

    protected function decodeRulesJson(?string $rulesJson): ?array
    {
        if (! $rulesJson || trim($rulesJson) === '') {
            return null;
        }

        $decoded = json_decode($rulesJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            throw ValidationException::withMessages([
                'rules_json' => 'Rules JSON must be valid JSON.',
            ]);
        }

        return $decoded;
    }
}
