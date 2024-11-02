<?php

namespace App\Filament\Pages;

use App\Models\Document;
use App\Models\GeneratedContent;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFilamentShield;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Colors\Color;
use App\Filament\Widgets\DashboardStatsOverview;
use App\Filament\Widgets\LatestDocumentsWidget;
use App\Filament\Widgets\RecentGeneratedContentWidget;
use App\Filament\Widgets\DocumentProcessingChart;
use App\Filament\Widgets\ApiUsageChart;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = null;
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = -2;

    public function getWidgets(): array
    {
        return [
            DashboardStatsOverview::class,
            // LatestDocumentsWidget::class,
            // RecentGeneratedContentWidget::class,
            DocumentProcessingChart::class,
            ApiUsageChart::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }
}