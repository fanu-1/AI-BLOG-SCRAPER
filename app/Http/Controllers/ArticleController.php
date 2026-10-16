<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function show(Article $article): View
    {
        abort_unless($article->status === 'published', 404);

        return view('article.show', [
            'article' => $article->load(['category', 'source', 'provider']),
            'relatedArticles' => Article::query()
                ->published()
                ->where('id', '!=', $article->id)
                ->when($article->category_id, fn ($query) => $query->where('category_id', $article->category_id))
                ->latest('published_at')
                ->take(3)
                ->get(),
            'categories' => Category::query()
                ->active()
                ->orderBy('sort_order')
                ->get(),
        ]);
    }
}
