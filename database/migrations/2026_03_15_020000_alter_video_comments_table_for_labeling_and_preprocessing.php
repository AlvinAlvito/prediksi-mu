<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_comments', function (Blueprint $table) {
            $table->string('language_code', 10)->nullable();
            $table->boolean('is_indonesian')->default(false);
            $table->decimal('language_score', 8, 4)->nullable();
            $table->text('processed_text')->nullable();
            $table->json('tokens')->nullable();
            $table->string('manual_sentiment', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('video_comments', function (Blueprint $table) {
            $table->dropColumn([
                'language_code',
                'is_indonesian',
                'language_score',
                'processed_text',
                'tokens',
                'manual_sentiment',
            ]);
        });
    }
};
