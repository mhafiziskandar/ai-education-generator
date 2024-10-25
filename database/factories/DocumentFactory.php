<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'file_path' => 'documents/' . $this->faker->word . '.pdf', // Simulate random file paths
            'document_type' => $this->faker->randomElement(['pdf', 'docx', 'pptx']), // Random document type
            'content' => $this->faker->paragraphs(3, true), // Simulate random content
            'is_processed' => $this->faker->boolean, // Random boolean for processed status
            'tokens_used' => $this->faker->numberBetween(100, 1000), // Random token usage
            'metadata' => json_encode(['extra_info' => $this->faker->sentence]), // Simulate JSON metadata
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}