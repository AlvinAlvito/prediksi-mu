<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment_preprocessings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained()->cascadeOnDelete();
            $table->text('cleaned_text')->nullable();
            $table->text('case_folded_text')->nullable();
            $table->text('normalized_text')->nullable();
            $table->json('tokenized_words')->nullable();
            $table->json('filtered_words')->nullable();
            $table->json('stemmed_words')->nullable();
            $table->text('final_text')->nullable();
            $table->string('language_code', 10)->nullable();
            $table->boolean('is_valid_for_analysis')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique('comment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_preprocessings');
    }
};
