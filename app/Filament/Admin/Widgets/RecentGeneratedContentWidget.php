<?php

namespace App\Filament\Admin\Widgets;

use App\Models\GeneratedContent;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentGeneratedContentWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Generated Content';
    
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(GeneratedContent::with('document')->latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('document.title')
                    ->label('Document')
                    ->searchable(),
                Tables\Columns\TextColumn::make('content_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('tokens_used')
                    ->numeric(),
                Tables\Columns\TextColumn::make('api_cost')
                    ->money('usd'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (GeneratedContent $record): string => route('filament.admin.resources.generated-contents.view', $record))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}