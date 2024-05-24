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
        Schema::create('mux_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('assetId');
            $table->string('playbackId')->nullable();
            $table->timestamps();
            $table->foreignUuid('lectureId')->references('id')->on('lectures')->onDelete('cascade');
            $table->unique('lectureId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mux_data');
    }
};
