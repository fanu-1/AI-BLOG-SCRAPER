<?php

namespace App\Http\Controllers\Admin;

use App\Models\ScrapedItem;
use Illuminate\View\View;

class ScrapedItemController extends BaseAdminController
{
    public function index(): View
    {
        $this->admin();

        return view('admin.scraped-items.index', [
            'items' => ScrapedItem::query()->with('source')->latest('fetched_at')->paginate(20),
        ]);
    }
}
