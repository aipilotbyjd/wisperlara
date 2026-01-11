<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('style_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('app_identifier', 100);
            $table->string('app_name', 100);
            $table->enum('style', ['formal', 'casual', 'extremely_casual'])->default('casual');
            $table->timestamps();
            $table->unique(['user_id', 'app_identifier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('style_preferences');
    }
};
