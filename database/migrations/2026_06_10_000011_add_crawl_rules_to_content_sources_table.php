<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_sources', function (Blueprint $table): void {
            $table->string('crawl_mode')->default('auto')->after('source_type');
            $table->string('sitemap_url')->nullable()->after('feed_url');
            $table->json('rules_json')->nullable()->after('language');
            $table->text('next_page_xpath')->nullable()->after('item_xpath');
            $table->unsignedInteger('max_depth')->default(3)->after('next_page_xpath');
            $table->unsignedInteger('max_pages')->default(50)->after('max_depth');
        });
    }

    public function down(): void
    {
        Schema::table('content_sources', function (Blueprint $table): void {
            $table->dropColumn([
                'crawl_mode',
                'sitemap_url',
                'rules_json',
                'next_page_xpath',
                'max_depth',
                'max_pages',
            ]);
        });
    }
};
