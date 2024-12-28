<?php

namespace App\Filament\Educator\Resources;

use App\Models\GeneratedContent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Filament\Educator\Resources\GeneratedContentResource\Pages\CreateGeneratedContent;
use App\Filament\Educator\Resources\GeneratedContentResource\Pages\EditGeneratedContent as PagesEditGeneratedContent;
use App\Filament\Educator\Resources\GeneratedContentResource\Pages\ListGeneratedContents as PagesListGeneratedContents;
use App\Filament\Educator\Resources\GeneratedContentResource\Pages\ViewGeneratedContent as PagesViewGeneratedContent;
use Filament\Tables\Actions\Action;

class GeneratedContentResource extends Resource
{
    protected static ?string $model = GeneratedContent::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'AI Content Generator';
    protected static ?int $navigationSort = 2;

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
                        // Lesson Plan specific fields
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
                                            ->suffix('hours'),
                                        Forms\Components\TextInput::make('duration_minutes')
                                            ->label('Minutes')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->maxValue(59)
                                            ->suffix('mins'),
                                    ])
                                    ->columns(2),
                                Forms\Components\Select::make('grade_level')
                                    ->label('Grade Level')
                                    ->options([
                                        'elementary' => 'Elementary School',
                                        'middle' => 'Middle School',
                                        'high' => 'High School',
                                        'college' => 'College',
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('teaching_method')
                                    ->label('Teaching Method')
                                    ->options([
                                        'lecture' => 'Traditional Lecture',
                                        'interactive' => 'Interactive Discussion',
                                        'group_work' => 'Group Work',
                                        'project_based' => 'Project-Based Learning',
                                        'flipped' => 'Flipped Classroom',
                                    ])
                                    ->required(),
                            ])
                            ->visible(fn (Get $get) => $get('content_type') === 'lesson_plan'),
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
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'failed',
                        'warning' => 'processing',
                        'success' => 'completed',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('tokens_used')
                    ->label('Tokens')
                    ->numeric(),
                Tables\Columns\TextColumn::make('api_cost')
                    ->money('usd')
                    ->label('Cost'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Action::make('view_pdf')
                    ->label('View PDF')
                    ->icon('heroicon-o-document')
                    ->url(fn (GeneratedContent $record) => $record->getFirstMedia('generated_pdfs')?->getUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (GeneratedContent $record) => $record->getFirstMedia('generated_pdfs') !== null),
                Action::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (GeneratedContent $record) => $record->getFirstMedia('generated_pdfs')?->getUrl(), shouldOpenInNewTab: true)
                    ->visible(fn (GeneratedContent $record) => $record->getFirstMedia('generated_pdfs') !== null),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('content_type')
                    ->options([
                        'quiz' => 'Quiz Questions',
                        'lesson_plan' => 'Lesson Plan',
                        'summary' => 'Summary',
                    ]),
            ])
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateHeading('No Generated Content Found')
            ->emptyStateDescription('No content has been generated yet. Please create new content.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->label('Generate Content'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => PagesListGeneratedContents::route('/'),
            'create' => CreateGeneratedContent::route('/create'),
            'view' => PagesViewGeneratedContent::route('/{record}'),
            'edit' => PagesEditGeneratedContent::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }
}