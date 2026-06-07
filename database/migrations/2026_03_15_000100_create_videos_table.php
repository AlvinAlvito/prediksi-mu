<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('youtube_video_id', 20)->unique();
            $table->string('url')->nullable();
            $table->string('title');
            $table->string('channel_title')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('youtube_comment_count')->nullable();
            $table->unsignedBigInteger('youtube_view_count')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
