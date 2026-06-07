<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_evaluation_class_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_evaluation_id')->constrained()->cascadeOnDelete();
            $table->string('label', 20);
            $table->decimal('precision', 8, 4)->nullable();
            $table->decimal('recall', 8, 4)->nullable();
            $table->decimal('f1_score', 8, 4)->nullable();
            $table->unsignedInteger('support')->default(0);
            $table->unsignedInteger('true_positive')->default(0);
            $table->unsignedInteger('false_positive')->default(0);
            $table->unsignedInteger('false_negative')->default(0);
            $table->unsignedInteger('true_negative')->default(0);
            $table->timestamps();

            $table->unique(['model_evaluation_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_evaluation_class_metrics');
    }
};
