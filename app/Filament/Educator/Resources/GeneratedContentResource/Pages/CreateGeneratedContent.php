<?php

namespace App\Filament\Educator\Resources\GeneratedContentResource\Pages;

use App\Filament\Educator\Resources\GeneratedContentResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\ContentGenerationService;

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