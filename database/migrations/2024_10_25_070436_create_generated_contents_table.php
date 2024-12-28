<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('content_type');
            $table->longText('content')->nullable();
            $table->string('status')->default('pending');
            $table->integer('tokens_used')->nullable();
            $table->decimal('api_cost', 10, 4)->nullable();
            // Lesson plan specific fields
            $table->integer('duration_hours')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('grade_level')->nullable();
            $table->string('teaching_method')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_contents');
    }
};
