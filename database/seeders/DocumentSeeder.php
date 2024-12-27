<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentSeeder extends Seeder
{
    public function run()
    {
        $filePath = storage_path('app/documents/sample.pdf');
        if (!file_exists($filePath)) {
            Storage::makeDirectory('documents');
            file_put_contents($filePath, 'Sample PDF content');
        }

        // Seed the documents
        Document::factory()->count(5)->create()->each(function (Document $document) use ($filePath) {
            $document->addMedia($filePath)
                     ->preservingOriginal()
                     ->toMediaCollection('documents');
        });
    }
}