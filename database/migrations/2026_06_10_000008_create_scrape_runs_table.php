<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scrape_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_source_id')->nullable()->constrained('content_sources')->nullOnDelete();
            $table->string('status')->default('started');
            $table->unsignedInteger('items_found')->default(0);
            $table->unsignedInteger('articles_created')->default(0);
            $table->unsignedInteger('articles_published')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrape_runs');
    }
};
