<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_samples', function (Blueprint $table) {
            $table->text('processed_text')->nullable();
            $table->json('tokens')->nullable();
            $table->string('labeler')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('training_samples', function (Blueprint $table) {
            $table->dropColumn([
                'processed_text',
                'tokens',
                'labeler',
                'notes',
                'is_active',
            ]);
        });
    }
};
