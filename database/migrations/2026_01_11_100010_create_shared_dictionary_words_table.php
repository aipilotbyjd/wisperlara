<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_dictionary_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('word', 255);
            $table->string('category', 50)->default('general');
            $table->string('pronunciation')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->index(['team_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_dictionary_words');
    }
};
