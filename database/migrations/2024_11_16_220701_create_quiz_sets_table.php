<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create quiz_sets table
        Schema::create('quiz_sets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('document_id');
            $table->string('title');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('document_id')->references('id')->on('documents');
        });

        // Modify existing quizzes table to reference quiz_sets
        Schema::table('quizzes', function (Blueprint $table) {
            $table->unsignedBigInteger('quiz_set_id')->after('id');
            $table->foreign('quiz_set_id')->references('id')->on('quiz_sets');
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