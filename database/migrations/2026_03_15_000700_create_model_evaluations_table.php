<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_run_id')->constrained()->cascadeOnDelete();
            $table->string('algorithm')->default('naive_bayes');
            $table->string('feature_extractor')->default('tf_idf');
            $table->unsignedInteger('training_sample_count')->default(0);
            $table->unsignedInteger('testing_sample_count')->default(0);
            $table->decimal('accuracy', 8, 4)->nullable();
            $table->decimal('precision', 8, 4)->nullable();
            $table->decimal('recall', 8, 4)->nullable();
            $table->decimal('f1_score', 8, 4)->nullable();
            $table->json('labels')->nullable();
            $table->json('confusion_matrix')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique('analysis_run_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_evaluations');
    }
};
