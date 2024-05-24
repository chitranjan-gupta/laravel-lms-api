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
        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('imageUrl')->nullable();
            $table->float('price')->nullable()->default(null);
            $table->boolean('isPublished')->default(false);
            $table->timestamps();
            $table->foreignUuid('categoryId')->nullable()->references('id')->on('categories')->onDelete('cascade');
            $table->foreignUuid('userId')->references('id')->on('users')->onDelete('cascade');
            $table->index('categoryId');
            $table->index('userId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
