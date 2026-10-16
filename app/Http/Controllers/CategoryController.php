<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Category $category): View
    {
        return view('category.show', [
            'category' => $category,
            'articles' => $category->articles()->published()->latest('published_at')->paginate(9),
        ]);
    }
}
