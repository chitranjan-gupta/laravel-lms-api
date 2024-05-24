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
        Schema::create('chapters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('description')->nullable()->default(null);
            $table->integer('position');
            $table->boolean('isPublished')->default(false);
            $table->boolean('isFree')->default(false);
            $table->float('duration');
            $table->foreignUuid('courseId')->references('id')->on('courses')->onDelete('cascade');
            $table->timestamps();
            $table->index('courseId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
