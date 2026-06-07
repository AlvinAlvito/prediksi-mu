<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('analysis_run_video_id')->nullable()->constrained('analysis_run_videos')->nullOnDelete();
            $table->foreignId('video_record_id')->nullable()->constrained('videos')->nullOnDelete();
            $table->string('youtube_comment_id')->nullable()->unique();
            $table->text('comment_url')->nullable();
            $table->unsignedInteger('like_count')->default(0);
            $table->unsignedInteger('reply_count')->default(0);
            $table->boolean('is_processed')->default(false);
            $table->json('raw_payload')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('analysis_run_video_id');
            $table->dropConstrainedForeignId('video_record_id');
            $table->dropUnique(['youtube_comment_id']);
            $table->dropColumn([
                'youtube_comment_id',
                'comment_url',
                'like_count',
                'reply_count',
                'is_processed',
                'raw_payload',
            ]);
        });
    }
};
