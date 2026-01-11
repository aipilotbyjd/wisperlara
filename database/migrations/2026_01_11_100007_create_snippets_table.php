<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snippets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('trigger_phrase', 100);
            $table->text('expansion_text');
            $table->string('category', 50)->default('general');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['user_id', 'category']);
            $table->index('team_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snippets');
    }
};
