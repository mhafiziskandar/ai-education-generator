<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateDocumentsToMediaLibrary extends Command
{
    protected $signature = 'documents:migrate-to-media-library';
    protected $description = 'Migrate existing documents to Spatie Media Library';

    public function handle()
    {
        $this->info('Starting document migration to Media Library...');
        
        $documents = Document::whereNotNull('file_path')->get();
        
        $bar = $this->output->createProgressBar($documents->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($documents as $document) {
            try {
                if (Storage::disk('public')->exists($document->file_path)) {
                    $path = Storage::disk('public')->path($document->file_path);
                    
                    $document->addMedia($path)
                            ->preservingOriginal()
                            ->toMediaCollection('document');
                    
                    $success++;
                } else {
                    $this->newLine();
                    $this->warn("File not found: {$document->file_path} for document: {$document->title}");
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error processing document {$document->title}: {$e->getMessage()}");
                $failed++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        
        $this->newLine(2);
        $this->info("Migration completed!");
        $this->info("Successfully migrated: $success documents");
        
        if ($failed > 0) {
            $this->warn("Failed to migrate: $failed documents");
        }
    }
}