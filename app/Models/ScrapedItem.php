<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapedItem extends Model
{
    protected $fillable = [
        'content_source_id',
        'fingerprint',
        'title',
        'meta_title',
        'meta_description',
        'url',
        'excerpt',
        'content',
        'image_url',
        'canonical_url',
        'author',
        'published_at',
        'http_status',
        'raw_payload',
        'fetched_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
            'published_at' => 'datetime',
            'fetched_at' => 'datetime',
            'http_status' => 'integer',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(ContentSource::class, 'content_source_id');
    }
}
