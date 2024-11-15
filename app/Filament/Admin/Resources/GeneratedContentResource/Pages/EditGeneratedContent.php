<?php

namespace App\Filament\Admin\Resources\GeneratedContentResource\Pages;

use App\Filament\Admin\Resources\GeneratedContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGeneratedContent extends EditRecord
{
    protected static string $resource = GeneratedContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
