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
        Schema::create('careers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('contact_no')->nullable();
            $table->string('contact_email')->nullable();
            $table->longText('description')->nullable();
            $table->string('location')->nullable();
            $table->string('salary_range')->nullable();
            $table->datetime('application_deadline')->nullable();
            $table->string('career_url')->nullable();
            $table->string('work_mode')->nullable();
            $table->datetime('date_posted')->nullable();
            $table->json('responsibilities')->nullable();
            $table->json('benefits')->nullable();
            $table->json('requirements')->nullable();
            $table->json('skills')->nullable();
            $table->string('level')->nullable();
            $table->string('experience')->nullable();
            $table->string('department')->nullable();
            $table->foreignUuid('companyId')->references('id')->on('companies')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('careers');
    }
};
