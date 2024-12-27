<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Document;
use App\Models\QuizSet;
use App\Models\Quiz;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        $student = User::where('email', 'student@email.com')->first();

        if (!$student) {
            echo "Error: Student account not found!\n";
            return;
        }

        $documents = [
            [
                'title' => 'Introduction to Programming',
                'document_type' => 'pdf',
                'user_id' => $student->id,
            ],
            [
                'title' => 'Database Management Systems',
                'document_type' => 'pdf',
                'user_id' => $student->id,
            ],
            [
                'title' => 'Web Development Basics',
                'document_type' => 'pdf',
                'user_id' => $student->id,
            ],
        ];

        foreach ($documents as $doc) {
            // Create a document without the file_path
            $document = Document::create($doc);

            // Add a media file using Spatie Media Library
            $document->addMedia(storage_path("app/demo_files/{$doc['title']}.pdf"))
                     ->preservingOriginal()
                     ->toMediaCollection('documents');

            // Create a quiz set for this document
            $quizSet = QuizSet::create([
                'user_id' => $student->id,
                'document_id' => $document->id,
                'title' => "Quiz for {$document->title}",
                'status' => 'active',
            ]);

            // Add questions to this quiz set
            $questions = match ($document->title) {
                'Introduction to Programming' => [
                    [
                        'question' => "What is a variable in programming?",
                        'correct_answer' => "A named storage location for holding data that can be modified during program execution.",
                        'status' => 'active',
                    ],
                    [
                        'question' => "What is a loop in programming?",
                        'correct_answer' => "A programming construct that repeats a block of code multiple times.",
                        'status' => 'completed',
                    ],
                ],
                'Database Management Systems' => [
                    [
                        'question' => "What is a primary key in a database?",
                        'correct_answer' => "A column or set of columns that uniquely identifies each row in a table.",
                        'status' => 'active',
                    ],
                    [
                        'question' => "What is SQL?",
                        'correct_answer' => "Structured Query Language - a standard language for managing and manipulating databases.",
                        'status' => 'completed',
                    ],
                ],
                'Web Development Basics' => [
                    [
                        'question' => "What does HTML stand for?",
                        'correct_answer' => "HyperText Markup Language - the standard markup language for creating web pages.",
                        'status' => 'active',
                    ],
                    [
                        'question' => "What is CSS used for?",
                        'correct_answer' => "Styling and formatting the visual presentation of HTML documents.",
                        'status' => 'completed',
                    ],
                ],
                default => [],
            };

            foreach ($questions as $question) {
                Quiz::create([
                    'quiz_set_id' => $quizSet->id,
                    'user_id' => $student->id,
                    'document_id' => $document->id,
                    'question' => $question['question'],
                    'correct_answer' => $question['correct_answer'],
                    'status' => $question['status'],
                ]);
            }
        }

        echo "Demo data created successfully!\n";
    }
}