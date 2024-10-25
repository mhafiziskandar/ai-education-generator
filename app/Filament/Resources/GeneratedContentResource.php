<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GeneratedContentResource\Pages;
use App\Filament\Resources\GeneratedContentResource\RelationManagers;
use App\Models\GeneratedContent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GeneratedContentResource extends Resource
{
    protected static ?string $model = GeneratedContent::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('content_type')
                            ->options([
                                'quiz' => 'Quiz',
                                'study_guide' => 'Study Guide',
                                'summary' => 'Summary',
                                'learning_path' => 'Learning Path',
                            ])
                            ->required(),
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpan('full'),
                        Forms\Components\TextInput::make('token_count')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('api_cost')
                            ->numeric()
                            ->prefix('$')
                            ->disabled(),
                    ])
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document.title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('content_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('token_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('api_cost')
                    ->money('usd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('content_type')
                    ->options([
                        'quiz' => 'Quiz',
                        'study_guide' => 'Study Guide',
                        'summary' => 'Summary',
                        'learning_path' => 'Learning Path',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGeneratedContents::route('/'),
            'create' => Pages\CreateGeneratedContent::route('/create'),
            'edit' => Pages\EditGeneratedContent::route('/{record}/edit'),
            'view' => Pages\ViewGeneratedContent::route('/{record}'),
        ];
    }
}