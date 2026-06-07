<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_run_id')->constrained()->cascadeOnDelete();
            $table->string('video_id', 20);
            $table->string('video_title');
            $table->string('author_name')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->text('original_text');
            $table->text('processed_text')->nullable();
            $table->json('tokens')->nullable();
            $table->string('predicted_sentiment', 20);
            $table->json('scores')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
