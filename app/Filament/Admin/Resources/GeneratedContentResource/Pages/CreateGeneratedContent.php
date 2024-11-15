<?php

namespace App\Filament\Admin\Resources\GeneratedContentResource\Pages;

use App\Filament\Admin\Resources\GeneratedContentResource;
use App\Services\ContentGeneratorService;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Document;

class CreateGeneratedContent extends CreateRecord
{
    protected static string $resource = GeneratedContentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $document = Document::findOrFail($data['document_id']);
        
        // Initialize the service
        $generator = app(ContentGeneratorService::class);
        
        // Generate the content and get the result
        $generatedContent = $generator->generateContent($document, $data['content_type']);
        
        // Return the data in the format expected by the form
        return [
            'document_id' => $document->id,
            'content_type' => $data['content_type'],
            'content' => $generatedContent->content,
            'token_used' => $generatedContent->token_used,
            'api_cost' => $generatedContent->api_cost,
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', ['record' => $this->record]);
    }
}
