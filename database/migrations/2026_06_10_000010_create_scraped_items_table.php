<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scraped_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_source_id')->constrained('content_sources')->cascadeOnDelete();
            $table->string('fingerprint')->unique();
            $table->string('title')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('url')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('image_url')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('author')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scraped_items');
    }
};
