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
            'user_id' => 1,
            'title' => $this->faker->sentence,
            'slug' => $this->faker->slug,
            'document_type' => $this->faker->randomElement(['pdf', 'docx', 'pptx']),
            'tags' => json_encode($this->faker->words(3)),
            'tokens_used' => $this->faker->numberBetween(100, 1000),
            'processing_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}