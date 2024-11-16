<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;

class QuizSeeder extends Seeder
{
    public function run()
    {
        $quizzes = [
            [
                'student_id' => 1, // Assuming you have a student with ID 1
                'document_id' => 1, // Assuming you have a document with ID 1
                'question' => 'What is photosynthesis?',
                'correct_answer' => 'The process by which plants convert light energy into chemical energy',
                'status' => 'active',
            ],
            [
                'student_id' => 1,
                'document_id' => 1,
                'question' => 'What is the capital of France?',
                'correct_answer' => 'Paris',
                'status' => 'active',
            ],
            [
                'student_id' => 1,
                'document_id' => 2,
                'question' => 'What is the formula for water?',
                'correct_answer' => 'H2O',
                'status' => 'active',
            ],
        ];

        foreach ($quizzes as $quiz) {
            Quiz::create($quiz);
        }
    }
}