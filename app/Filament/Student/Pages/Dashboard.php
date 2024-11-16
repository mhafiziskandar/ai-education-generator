<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Dashboard as BasePage;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use App\Models\Document;
use App\Filament\Student\Widgets\StatsOverviewWidget;
use App\Filament\Student\Widgets\RecentDocumentsWidget;
use App\Filament\Student\Widgets\UpcomingQuizzesWidget;

class Dashboard extends BasePage
{
    use HasFiltersForm;

    protected static ?int $navigationSort = -2;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Student Dashboard';
    protected static ?string $title = 'Student Dashboard';

    public function getColumns(): int | array
    {
        return 2;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            RecentDocumentsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            UpcomingQuizzesWidget::class,
        ];
    }
}