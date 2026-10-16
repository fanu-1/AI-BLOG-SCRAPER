<?php

namespace App\Http\Controllers\Admin;

use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends BaseAdminController
{
    public function index(): View
    {
        $this->admin();

        return view('admin.articles.index', [
            'articles' => Article::query()->with(['category', 'source', 'provider'])->latest()->paginate(12),
        ]);
    }

    public function edit(Article $article): View
    {
        $this->admin();

        return view('admin.articles.edit', [
            'article' => $article->load(['category', 'source', 'provider']),
        ]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $this->admin();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'body_html' => ['required', 'string'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,scheduled'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $article->update([
            ...$data,
            'is_featured' => $request->boolean('is_featured', false),
            'published_at' => $data['status'] === 'published' && ! $article->published_at ? now() : $article->published_at,
        ]);

        return redirect()->route('admin.articles.edit', $article)->with('status', 'Article updated.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->admin();
        $article->delete();

        return redirect()->route('admin.articles.index')->with('status', 'Article deleted.');
    }

    public function publish(Article $article): RedirectResponse
    {
        $this->admin();
        $article->update([
            'status' => 'published',
            'published_at' => $article->published_at ?: now(),
        ]);

        return back()->with('status', 'Article published.');
    }

    public function unpublish(Article $article): RedirectResponse
    {
        $this->admin();
        $article->update([
            'status' => 'draft',
        ]);

        return back()->with('status', 'Article moved to draft.');
    }
}
