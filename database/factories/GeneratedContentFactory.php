<?php

namespace Database\Factories;

use App\Models\GeneratedContent;
use App\Models\User; // Ensure this is imported
use Illuminate\Database\Eloquent\Factories\Factory;

class GeneratedContentFactory extends Factory
{
    protected $model = GeneratedContent::class;

    public function definition()
    {
        return [
            'document_id' => null, // This will be set during seeding
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(), // Assign an existing user or create one
            'content_type' => $this->faker->randomElement(['summary', 'notes']),
            'content' => $this->faker->paragraphs(3, true),
            'api_cost' => $this->faker->randomFloat(2, 0, 1),
            'is_published' => $this->faker->boolean,
            'tokens_used' => $this->faker->numberBetween(100, 1000),
            'metadata' => json_encode(['extra_info' => $this->faker->sentence]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}