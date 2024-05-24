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
        Schema::create('lectures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('description')->nullable()->default(null);
            $table->string('videoUrl')->nullable();
            $table->integer('position');
            $table->boolean('isPublished')->default(false);
            $table->boolean('isFree')->default(false);
            $table->float('duration');
            $table->uuid('courseId');
            $table->foreignUuid('chapterId')->references('id')->on('chapters')->onDelete('cascade');
            $table->timestamps();
            $table->index('chapterId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lectures');
    }
};
