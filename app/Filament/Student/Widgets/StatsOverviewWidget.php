<?php 

namespace App\Filament\Student\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Document;
use App\Models\Quiz;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Available Materials', Document::count())
                ->description('Total learning materials')
                ->icon('heroicon-o-document-text'),
            Stat::make('Completed Quizzes', Quiz::where('user_id', auth()->id())->where('status', 'completed')->count())
                ->description('Quizzes you have finished')
                ->icon('heroicon-o-check-circle'),
            Stat::make('Average Score', '85%')
                ->description('Your quiz performance')
                ->icon('heroicon-o-academic-cap'),
        ];
    }
}