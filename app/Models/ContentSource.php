<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentSource extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'source_type',
        'crawl_mode',
        'source_url',
        'feed_url',
        'sitemap_url',
        'description',
        'category_id',
        'ai_provider_id',
        'language',
        'rules_json',
        'title_xpath',
        'content_xpath',
        'excerpt_xpath',
        'link_xpath',
        'image_xpath',
        'item_xpath',
        'next_page_xpath',
        'max_depth',
        'max_pages',
        'auto_publish',
        'max_items',
        'is_active',
        'last_scraped_at',
    ];

    protected function casts(): array
    {
        return [
            'auto_publish' => 'boolean',
            'is_active' => 'boolean',
            'max_items' => 'integer',
            'max_depth' => 'integer',
            'max_pages' => 'integer',
            'last_scraped_at' => 'datetime',
            'rules_json' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(ScrapeRun::class);
    }

    public function scrapedItems(): HasMany
    {
        return $this->hasMany(ScrapedItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
