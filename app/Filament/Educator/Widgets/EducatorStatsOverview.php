<?php 

namespace App\Filament\Educator\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Document;
use App\Models\GeneratedContent;

class EducatorStatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $userId = auth()->id();

        return [
            Card::make('Total Documents', Document::where('user_id', $userId)->count())
                ->description('Your created documents')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),
            Card::make('Published Documents', Document::where('user_id', $userId)
                    // ->where('status', 'published')
                    ->count()
                )
                ->description('Documents published')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Card::make('AI Generated Content', GeneratedContent::where('user_id', $userId)->count())
                ->description('AI-generated materials')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('success'),
        ];
    }
}