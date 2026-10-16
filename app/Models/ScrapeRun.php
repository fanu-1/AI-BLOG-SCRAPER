<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapeRun extends Model
{
    protected $fillable = [
        'content_source_id',
        'status',
        'items_found',
        'articles_created',
        'articles_published',
        'started_at',
        'finished_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'items_found' => 'integer',
            'articles_created' => 'integer',
            'articles_published' => 'integer',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(ContentSource::class, 'content_source_id');
    }
}
