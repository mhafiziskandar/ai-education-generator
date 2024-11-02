<?php

namespace App\Filament\Resources\DocumentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class GeneratedContentsRelationManager extends RelationManager
{
    protected static string $relationship = 'generatedContents';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('content_type')
                    ->options([
                        'quiz' => 'Quiz Questions',
                        'lesson_plan' => 'Lesson Plan',
                        'summary' => 'Summary',
                    ])
                    ->required(),
                Forms\Components\RichEditor::make('content')
                    ->required()
                    ->columnSpan('full'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('content_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('token_used')
                    ->numeric(),
                Tables\Columns\TextColumn::make('api_cost')
                    ->money('usd'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('content_type')
                    ->options([
                        'quiz' => 'Quiz Questions',
                        'lesson_plan' => 'Lesson Plan',
                        'summary' => 'Summary',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}