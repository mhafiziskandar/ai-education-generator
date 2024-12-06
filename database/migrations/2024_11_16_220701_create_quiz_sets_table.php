<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quiz_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('quiz_set_id')->after('id')->constrained()->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['quiz_set_id']);
            $table->dropColumn('quiz_set_id');
        });
        
        Schema::dropIfExists('quiz_sets');
    }
};