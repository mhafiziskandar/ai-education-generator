<?php

namespace App\Filament\Student\Resources;

use App\Models\QuizSet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;
use App\Filament\Student\Resources\QuizSetResource\Pages;

class QuizSetResource extends Resource
{
    protected static ?string $model = QuizSet::class;
    
    protected static ?string $slug = 'quizzes';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Learning';
    protected static ?string $navigationLabel = 'My Quizzes';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document.title')
                    ->label('Document')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                Tables\Columns\TextColumn::make('questions_count')
                    ->counts('questions')
                    ->label('Total Questions'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'active',
                        'danger' => 'completed'
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Generated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'pending' => 'Pending',
                    ]),
                Tables\Filters\SelectFilter::make('document')
                    ->relationship('document', 'title')
            ])
            ->actions([
                Tables\Actions\Action::make('take_quiz')
                    ->label('Take Quiz')
                    ->icon('heroicon-o-play')
                    ->url(fn (QuizSet $record): string => static::getUrl('take', ['record' => $record]))
                    ->visible(fn (QuizSet $record): bool => $record->status === 'active'),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateHeading('No quizzes yet')
            ->emptyStateDescription('Questions will appear here once they are generated from your documents.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizSets::route('/'),
            'view' => Pages\ViewQuizSet::route('/{record}'),
            'take' => Pages\TakeQuizSet::route('/{record}/take'),
        ];
    }
}