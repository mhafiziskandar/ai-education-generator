<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('quiz_sets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['document_id']);
            
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
            
            $table->foreign('document_id')
                  ->references('id')
                  ->on('documents')
                  ->cascadeOnDelete();
        });

        Schema::table('quizzes', function (Blueprint $table) {
            if (Schema::hasColumn('quizzes', 'document_id')) {
                $table->dropForeign(['document_id']);
                $table->dropColumn('document_id');
            }
            
            $table->dropForeign(['quiz_set_id']);
            $table->foreign('quiz_set_id')
                  ->references('id')
                  ->on('quiz_sets')
                  ->cascadeOnDelete();
        });
    }

    public function down()
    {

    }
};