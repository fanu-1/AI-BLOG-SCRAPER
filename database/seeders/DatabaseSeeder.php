<?php

namespace Database\Seeders;

use App\Models\AiProvider;
use App\Models\Category;
use App\Models\ContentSource;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'is_admin' => true,
            ]
        );

        Category::query()->firstOrCreate([
            'slug' => 'technology',
        ], [
            'name' => 'Technology',
            'description' => 'Tech news, AI updates, and product insights.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Category::query()->firstOrCreate([
            'slug' => 'business',
        ], [
            'name' => 'Business',
            'description' => 'Markets, startups, and growth stories.',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        AiProvider::query()->firstOrCreate([
            'driver' => 'gemini',
            'name' => 'Gemini Editorial',
        ], [
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'api_key' => null,
            'model' => 'gemini-1.5-flash',
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'system_prompt' => 'You write polished editorial JSON.',
            'is_active' => false,
        ]);

        ContentSource::query()->firstOrCreate([
            'slug' => 'bbc-news',
        ], [
            'name' => 'BBC News',
            'source_type' => 'rss',
            'source_url' => 'https://www.bbc.com/news',
            'feed_url' => 'https://feeds.bbci.co.uk/news/rss.xml',
            'category_id' => Category::query()->where('slug', 'technology')->value('id'),
            'ai_provider_id' => null,
            'language' => 'en',
            'auto_publish' => false,
            'max_items' => 50,
            'is_active' => true,
        ]);
    }
}
