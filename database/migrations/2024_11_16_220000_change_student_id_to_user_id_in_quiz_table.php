<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            // First drop the foreign key if it exists
            $table->dropForeign(['student_id']);
            
            // Rename the column
            $table->renameColumn('student_id', 'user_id');
            
            // Add new foreign key
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->renameColumn('user_id', 'student_id');
            $table->foreign('student_id')->references('id')->on('users');
        });
    }
};