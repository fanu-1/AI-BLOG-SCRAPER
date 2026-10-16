<?php

use App\Services\ContentSyncService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('content:sync', function (): void {
    $queued = app(ContentSyncService::class)->queueAll();

    if ($queued === 0) {
        $this->warn('No active content sources found.');

        return;
    }

    $this->info("Queued {$queued} source crawl job(s).");
})->purpose('Scrape sources and store raw items.');

Schedule::command('content:sync')->dailyAt('02:00');

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
