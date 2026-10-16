<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    protected $fillable = [
        'category_id',
        'content_source_id',
        'ai_provider_id',
        'title',
        'slug',
        'excerpt',
        'body_html',
        'seo_title',
        'seo_description',
        'featured_image_url',
        'source_url',
        'source_title',
        'source_payload',
        'ai_payload',
        'status',
        'is_featured',
        'published_at',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'source_payload' => 'array',
            'ai_payload' => 'array',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'generated_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(ContentSource::class, 'content_source_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
