<?php

namespace App\Filament\Student\Resources\DocumentResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuizSetsRelationManager extends RelationManager
{
    protected static string $relationship = 'quizSets';
    protected static ?string $title = 'Quizzes';
    protected static ?string $modelLabel = 'Quiz Set';
    protected static ?string $pluralModelLabel = 'Quiz Sets';
    protected static ?string $recordTitleAttribute = 'title';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Quiz Set')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'completed' => 'info',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('quizzes_count')
                    ->counts('quizzes')
                    ->label('Questions'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->emptyStateHeading('No quizzes available yet')
            ->emptyStateDescription('Your quizzes will appear here once they are assigned.');
    }
}