<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Document;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class DocumentProcessingChart extends ChartWidget
{
    protected static ?string $heading = 'Document Processing Overview';
    
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = Trend::model(Document::class)
            ->between(
                start: now()->subDays(7),
                end: now(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Documents Processed',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#10B981',
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