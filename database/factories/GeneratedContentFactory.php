<?php

namespace Database\Factories;

use App\Models\GeneratedContent;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Document;

class GeneratedContentFactory extends Factory
{
    protected $model = GeneratedContent::class;

    public function definition()
    {
        return [
            'document_id' => Document::factory(), // Create a related document
            'content_type' => $this->faker->randomElement(['quiz', 'study_guide', 'summary', 'learning_path']),
            'content' => $this->faker->paragraphs(3, true),
            'api_cost' => $this->faker->randomFloat(2, 0, 10), // Random API cost
            'is_published' => $this->faker->boolean, // Random boolean
            'tokens_used' => $this->faker->numberBetween(100, 1000), // Random tokens used
            'metadata' => json_encode(['extra_info' => $this->faker->sentence]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}