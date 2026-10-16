<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_sources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ai_provider_id')->nullable()->constrained('ai_providers')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('source_type')->default('html');
            $table->string('source_url');
            $table->string('feed_url')->nullable();
            $table->string('language', 10)->default('en');
            $table->text('description')->nullable();
            $table->string('title_xpath')->nullable();
            $table->text('content_xpath')->nullable();
            $table->text('excerpt_xpath')->nullable();
            $table->text('link_xpath')->nullable();
            $table->text('image_xpath')->nullable();
            $table->text('item_xpath')->nullable();
            $table->boolean('auto_publish')->default(false);
            $table->unsignedInteger('max_items')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_sources');
    }
};
