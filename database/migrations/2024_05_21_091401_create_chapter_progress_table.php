<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chapter_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('userId');
            $table->boolean('isCompleted')->default(false);
            $table->timestamps();
            $table->foreignUuid('chapterId')->references('id')->on('chapters')->onDelete('cascade');
            $table->unique(['userId', 'chapterId']);
            $table->index('chapterId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapter_progress');
    }
};
