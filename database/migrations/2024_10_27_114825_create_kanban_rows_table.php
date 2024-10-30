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
        Schema::create('kanban_rows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('subtitle');
            $table->integer('position');
            $table->string('status')->nullable();
            $table->datetime('applied_date')->nullable();
            $table->datetime('rejected_date')->nullable();
            $table->longText('notes')->nullable();
            $table->json('tags')->nullable();
            $table->foreignUuid('kanbanColumnId')->references('id')->on('kanban_columns')->onDelete('cascade');
            $table->foreignUuid('careerId')->nullable()->references('id')->on('careers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban_rows');
    }
};
