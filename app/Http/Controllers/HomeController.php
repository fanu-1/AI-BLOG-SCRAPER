<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('welcome', [
            'featuredArticles' => Article::query()
                ->published()
                ->with('category')
                ->latest('published_at')
                ->take(3)
                ->get(),
            'latestArticles' => Article::query()
                ->published()
                ->with('category')
                ->latest('published_at')
                ->paginate(6),
            'categories' => Category::query()
                ->active()
                ->orderBy('sort_order')
                ->get(),
        ]);
    }
}
