<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Document;
use Database\Seeders\DocumentSeeder;
use App\Models\GeneratedContent;

class GeneratedContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Ensure there are some documents in the database
        if (Document::count() === 0) {
            $this->call(DocumentSeeder::class); // Seed documents first
        }

        $documents = Document::all();

        // Create 10 dummy generated contents linked to random documents
        foreach ($documents as $document) {
            GeneratedContent::factory()->count(2)->create([
                'document_id' => $document->id,
            ]);
        }
    }
}
