<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analysis_runs', function (Blueprint $table) {
            $table->string('algorithm')->nullable();
            $table->string('feature_extractor')->nullable();
            $table->unsignedInteger('training_sample_count')->default(0);
            $table->unsignedInteger('testing_sample_count')->default(0);
            $table->json('run_config')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('analysis_runs', function (Blueprint $table) {
            $table->dropColumn([
                'algorithm',
                'feature_extractor',
                'training_sample_count',
                'testing_sample_count',
                'run_config',
                'started_at',
                'finished_at',
            ]);
        });
    }
};
