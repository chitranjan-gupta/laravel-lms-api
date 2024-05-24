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
        Schema::create('lecture_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('userId');
            $table->boolean('isCompleted')->default(false);
            $table->timestamps();
            $table->foreignUuid('lectureId')->references('id')->on('lectures')->onDelete('cascade');
            $table->unique(['userId', 'lectureId']);
            $table->index('lectureId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecture_progress');
    }
};
