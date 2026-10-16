<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiProvider extends Model
{
    protected $fillable = [
        'name',
        'driver',
        'base_url',
        'api_key',
        'model',
        'temperature',
        'max_tokens',
        'system_prompt',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'temperature' => 'decimal:2',
            'max_tokens' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function sources(): HasMany
    {
        return $this->hasMany(ContentSource::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
