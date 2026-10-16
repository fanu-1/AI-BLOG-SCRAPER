<?php

namespace App\Jobs;

use App\Models\ContentSource;
use App\Services\ContentSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncContentSourceJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;

    public function __construct(public int $sourceId)
    {
    }

    public function handle(ContentSyncService $syncService): void
    {
        $source = ContentSource::query()->find($this->sourceId);

        if (! $source || ! $source->is_active) {
            return;
        }

        $syncService->syncSource($source);
    }
}
