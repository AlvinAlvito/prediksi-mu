<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_samples', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->string('sentiment', 20);
            $table->string('source')->default('seed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_samples');
    }
};
