<?php

namespace App\Filament\Student\Resources\DocumentResource\Pages;

use App\Filament\Student\Resources\DocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        
        return $data;
    }
}