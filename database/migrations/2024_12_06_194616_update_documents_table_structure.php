<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['metadata', 'content', 'is_processed']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->string('slug')->after('title');
            $table->json('tags')->nullable()->after('document_type');
            
            $table->string('processing_status')->default('pending')->after('tokens_used');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->json('metadata')->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_processed')->default(false);
            
            $table->dropColumn(['slug', 'tags', 'processing_status']);
        });
    }
};