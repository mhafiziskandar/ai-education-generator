<?php

namespace App\Filament\Admin\Resources\GeneratedContentResource\Pages;

use App\Filament\Admin\Resources\GeneratedContentResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewGeneratedContent extends ViewRecord
{
    protected static string $resource = GeneratedContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    return response()->download(
                        storage_path('app/public/' . $this->record->file_path)
                    );
                }),
        ];
    }
}