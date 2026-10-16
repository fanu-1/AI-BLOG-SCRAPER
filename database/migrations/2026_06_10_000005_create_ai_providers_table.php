<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_providers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('driver');
            $table->string('base_url')->nullable();
            $table->text('api_key')->nullable();
            $table->string('model');
            $table->decimal('temperature', 4, 2)->default(0.70);
            $table->unsignedInteger('max_tokens')->default(2000);
            $table->text('system_prompt')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_providers');
    }
};
