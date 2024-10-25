<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use App\Models\GeneratedContent;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Documents', Document::count())
                ->description('Total uploaded documents')
                ->descriptionIcon('heroicon-m-document')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('Generated Contents', GeneratedContent::count())
                ->description('Total generated contents')
                ->descriptionIcon('heroicon-m-document-text')
                ->chart([15, 4, 17, 7, 2, 10, 3])
                ->color('primary'),

            Stat::make('Total API Cost', '$' . number_format(GeneratedContent::sum('api_cost'), 2))
                ->description('Total API usage cost')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('warning'),
        ];
    }
}