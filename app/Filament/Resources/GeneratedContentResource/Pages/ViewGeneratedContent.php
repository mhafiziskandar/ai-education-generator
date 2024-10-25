<?php

namespace App\Filament\Resources\GeneratedContentResource\Pages;

use App\Filament\Resources\GeneratedContentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGeneratedContent extends ViewRecord
{
    protected static string $resource = GeneratedContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}