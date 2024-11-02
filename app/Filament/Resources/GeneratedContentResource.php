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
use Filament\Forms\Get;

class GeneratedContentResource extends Resource
{
    protected static ?string $model = GeneratedContent::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('document_id')
                            ->relationship('document', 'title')
                            ->required(),
                        Forms\Components\Select::make('content_type')
                            ->options([
                                'quiz' => 'Quiz Questions',
                                'lesson_plan' => 'Lesson Plan',
                                'summary' => 'Summary',
                            ])
                            ->live()
                            ->required(),
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('duration_hours')
                                            ->label('Hours')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(0)
                                            ->maxValue(24)
                                            ->suffix('hours')
                                            ->visible(fn (Get $get) => $get('content_type') === 'lesson_plan'),
                                        Forms\Components\TextInput::make('duration_minutes')
                                            ->label('Minutes')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->maxValue(59)
                                            ->suffix('mins')
                                            ->visible(fn (Get $get) => $get('content_type') === 'lesson_plan'),
                                    ])
                                    ->columns(2)
                                    ->visible(fn (Get $get) => $get('content_type') === 'lesson_plan'),
                                Forms\Components\Select::make('grade_level')
                                    ->label('Grade Level')
                                    ->options([
                                        'elementary' => 'Elementary School',
                                        'middle' => 'Middle School',
                                        'high' => 'High School',
                                        'college' => 'College',
                                    ])
                                    ->visible(fn (Get $get) => $get('content_type') === 'lesson_plan')
                                    ->required(),
                            ]),
                        Forms\Components\Toggle::make('include_images')
                            ->label('Include Images')
                            ->default(true),
                        // Forms\Components\RichEditor::make('content')
                        //     ->required()
                        //     ->columnSpanFull(),
                        // Forms\Components\KeyValue::make('settings')
                        //     ->label('Generation Settings'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document.title')
                    ->label('Document Title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('content_type')
                    ->label('Content Type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('content_type')
                    ->options([
                        'quiz' => 'Quiz Questions',
                        'lesson_plan' => 'Lesson Plan',
                        'summary' => 'Summary',
                    ]),
            ])
            ->emptyStateIcon('heroicon-o-academic-cap') // Icon for empty state
            ->emptyStateHeading('No Generated Content Found') // Heading for empty state
            ->emptyStateDescription('No content has been generated yet. Please create new content.') // Description for empty state
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->label('Generate Content'), // Call to action for empty state
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