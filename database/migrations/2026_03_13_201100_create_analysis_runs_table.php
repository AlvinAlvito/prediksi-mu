<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_runs', function (Blueprint $table) {
            $table->id();
            $table->string('source')->default('web-ui');
            $table->json('source_links');
            $table->json('video_ids');
            $table->json('video_metadata')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedInteger('total_comments')->default(0);
            $table->unsignedInteger('analyzed_comments')->default(0);
            $table->unsignedInteger('positive_count')->default(0);
            $table->unsignedInteger('negative_count')->default(0);
            $table->unsignedInteger('neutral_count')->default(0);
            $table->decimal('accuracy', 8, 4)->nullable();
            $table->decimal('precision', 8, 4)->nullable();
            $table->decimal('recall', 8, 4)->nullable();
            $table->decimal('f1_score', 8, 4)->nullable();
            $table->json('evaluation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_runs');
    }
};
