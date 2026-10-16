<?php

namespace App\Services;

use App\Jobs\SyncContentSourceJob;
use App\Models\ContentSource;
use App\Models\ScrapeRun;
use App\Models\ScrapedItem;
use Throwable;

class ContentSyncService
{
    public function __construct(protected ScraperService $scraperService)
    {
    }

    public function syncAll(): array
    {
        $results = [];

        ContentSource::query()
            ->active()
            ->with(['category', 'provider'])
            ->orderBy('id')
            ->get()
            ->each(function (ContentSource $source) use (&$results): void {
                $results[] = $this->syncSource($source);
            });

        return $results;
    }

    public function queueAll(): int
    {
        $count = 0;

        ContentSource::query()
            ->active()
            ->orderBy('id')
            ->get()
            ->each(function (ContentSource $source) use (&$count): void {
                SyncContentSourceJob::dispatch($source->id);
                $count++;
            });

        return $count;
    }

    public function queueSource(ContentSource $source): void
    {
        SyncContentSourceJob::dispatch($source->id);
    }

    public function syncSource(ContentSource $source): array
    {
        $run = ScrapeRun::create([
            'content_source_id' => $source->id,
            'status' => 'running',
            'started_at' => now(),
        ]);

        $summary = [
            'source' => $source->name,
            'items_found' => 0,
            'stored' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        try {
            $items = $this->scraperService->scrape($source);
            $summary['items_found'] = count($items);

            foreach ($items as $item) {
                $result = $this->storeItem($source, $item);
                $summary[$result] = $summary[$result] + 1;
            }

            $source->forceFill(['last_scraped_at' => now()])->save();

            $run->update([
                'status' => 'completed',
                'items_found' => $summary['items_found'],
                'articles_created' => 0,
                'articles_published' => 0,
                'finished_at' => now(),
                'notes' => null,
            ]);
        } catch (Throwable $throwable) {
            $summary['errors'][] = $throwable->getMessage();

            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'notes' => $throwable->getMessage(),
            ]);
        }

        return $summary;
    }

    protected function storeItem(ContentSource $source, array $item): string
    {
        if (empty($item['fingerprint'])) {
            return 'skipped';
        }

        $existing = ScrapedItem::query()
            ->where('fingerprint', $item['fingerprint'])
            ->where('content_source_id', $source->id)
            ->first();

        if ($existing) {
            $existing->update([
                'title' => $item['title'] ?? $existing->title,
                'meta_title' => $item['meta_title'] ?? $existing->meta_title,
                'meta_description' => $item['meta_description'] ?? $existing->meta_description,
                'url' => $item['url'] ?? $existing->url,
                'excerpt' => $item['excerpt'] ?? $existing->excerpt,
                'content' => $item['content'] ?? $existing->content,
                'image_url' => $item['image_url'] ?? $existing->image_url,
                'canonical_url' => $item['canonical_url'] ?? $existing->canonical_url,
                'author' => $item['author'] ?? $existing->author,
                'published_at' => $item['published_at'] ?? $existing->published_at,
                'http_status' => $item['http_status'] ?? $existing->http_status,
                'raw_payload' => $item['raw_payload'] ?? $existing->raw_payload,
                'fetched_at' => now(),
                'error_message' => null,
            ]);

            return 'updated';
        }

        ScrapedItem::create([
            'content_source_id' => $source->id,
            'fingerprint' => $item['fingerprint'],
            'title' => $item['title'] ?? null,
            'meta_title' => $item['meta_title'] ?? null,
            'meta_description' => $item['meta_description'] ?? null,
            'url' => $item['url'] ?? null,
            'excerpt' => $item['excerpt'] ?? null,
            'content' => $item['content'] ?? null,
            'image_url' => $item['image_url'] ?? null,
            'canonical_url' => $item['canonical_url'] ?? null,
            'author' => $item['author'] ?? null,
            'published_at' => $item['published_at'] ?? null,
            'http_status' => $item['http_status'] ?? null,
            'raw_payload' => $item['raw_payload'] ?? null,
            'fetched_at' => now(),
        ]);

        return 'stored';
    }
}
