<?php

namespace App\Filament\Admin\Widgets;

use App\Models\GeneratedContent;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ApiUsageChart extends ChartWidget
{
    protected static ?string $heading = 'API Usage Cost';
    
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = Trend::model(GeneratedContent::class)
            ->between(
                start: now()->subDays(7),
                end: now(),
            )
            ->perDay()
            ->sum('api_cost');

        return [
            'datasets' => [
                [
                    'label' => 'API Cost ($)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#F59E0B',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}