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
        $student = User::where('email', 'student@example.com')->first();

        if (!$student) {
            echo "Error: Student account not found!\n";
            return;
        }

        $documents = [
            [
                'title' => 'Introduction to Programming',
                'document_type' => 'pdf',
                'file_path' => 'documents/programming.pdf',
                'user_id' => $student->id,
            ],
            [
                'title' => 'Database Management Systems',
                'document_type' => 'pdf',
                'file_path' => 'documents/database.pdf',
                'user_id' => $student->id,
            ],
            [
                'title' => 'Web Development Basics',
                'document_type' => 'pdf',
                'file_path' => 'documents/web.pdf',
                'user_id' => $student->id,
            ],
        ];

        foreach ($documents as $doc) {
            $document = Document::create($doc);

            // Create a quiz set for this document
            $quizSet = QuizSet::create([
                'user_id' => $student->id,
                'document_id' => $document->id,
                'title' => "Quiz for {$document->title}",
                'status' => 'active'
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
                    [
                        'question' => "What is the difference between while and for loops?",
                        'correct_answer' => "While loops continue until a condition is false, for loops iterate a specific number of times.",
                        'status' => 'active',
                    ],
                    [
                        'question' => "What is a function in programming?",
                        'correct_answer' => "A reusable block of code that performs a specific task.",
                        'status' => 'active',
                    ],
                    [
                        'question' => "What is an array?",
                        'correct_answer' => "A data structure that stores multiple values in a single variable.",
                        'status' => 'active',
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
                    [
                        'question' => "What is a JOIN in SQL?",
                        'correct_answer' => "A clause used to combine rows from two or more tables based on a related column.",
                        'status' => 'active',
                    ],
                    [
                        'question' => "What is normalization in databases?",
                        'correct_answer' => "The process of organizing data to minimize redundancy and dependency by organizing fields and tables of a database.",
                        'status' => 'active',
                    ],
                    [
                        'question' => "What is a foreign key?",
                        'correct_answer' => "A field in one table that refers to the primary key in another table.",
                        'status' => 'active',
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
                    [
                        'question' => "What is the difference between inline and block elements?",
                        'correct_answer' => "Inline elements flow within text and only take up as much width as necessary, while block elements start on new lines and take up the full width available.",
                        'status' => 'active',
                    ],
                    [
                        'question' => "What is JavaScript?",
                        'correct_answer' => "A programming language that enables interactive web pages and is an essential part of web applications.",
                        'status' => 'active',
                    ],
                    [
                        'question' => "What is responsive design?",
                        'correct_answer' => "An approach to web design that makes web pages render well on a variety of devices and window or screen sizes.",
                        'status' => 'active',
                    ],
                ],
                default => []
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