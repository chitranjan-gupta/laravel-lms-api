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
        Schema::create('google_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('google_id')->unique();  // Google user ID
            $table->string('avatar')->nullable(); // The Google Avatar
            $table->string('access_token')->nullable();  // The access token (expires in 1 hour)
            $table->string('refresh_token')->nullable();  // The refresh token
            $table->string('token_type')->nullable();  // Token type, e.g., 'bearer'
            $table->timestamp('expires_at')->nullable();  // When the access token expires
            $table->foreignUuid('userId')->nullable()->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_details');
    }
};
