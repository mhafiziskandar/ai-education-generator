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
        $data['user_id'] = auth()->id();
        $data['status'] = 'pending';
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $contentService = app(ContentGenerationService::class);
        
        try {
            $generatedContent = $contentService->generateContent($this->record);
            
            $this->record->update([
                'content' => $generatedContent,
            ]);
        } catch (\Exception $e) {
            // Error handling is done in the service
            report($e);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
