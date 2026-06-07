<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_run_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('fetched_comment_count')->default(0);
            $table->unsignedInteger('analyzed_comment_count')->default(0);
            $table->json('snapshot')->nullable();
            $table->timestamps();

            $table->unique(['analysis_run_id', 'video_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_run_videos');
    }
};
