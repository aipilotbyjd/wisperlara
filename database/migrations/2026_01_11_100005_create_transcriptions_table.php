<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transcriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('original_text');
            $table->text('polished_text')->nullable();
            $table->string('app_context', 100)->nullable();
            $table->enum('style', ['formal', 'casual', 'extremely_casual'])->nullable();
            $table->string('language', 10)->default('en');
            $table->integer('duration_seconds')->default(0);
            $table->integer('word_count')->default(0);
            $table->string('transcription_provider', 50)->nullable();
            $table->string('polishing_provider', 50)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transcriptions');
    }
};
