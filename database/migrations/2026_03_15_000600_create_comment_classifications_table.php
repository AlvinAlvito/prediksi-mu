<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment_classifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('comment_id')->constrained()->cascadeOnDelete();
            $table->string('algorithm')->default('naive_bayes');
            $table->string('feature_extractor')->default('tf_idf');
            $table->string('predicted_sentiment', 20);
            $table->string('actual_sentiment', 20)->nullable();
            $table->decimal('confidence_score', 12, 8)->nullable();
            $table->json('scores')->nullable();
            $table->timestamps();

            $table->unique(['analysis_run_id', 'comment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_classifications');
    }
};
