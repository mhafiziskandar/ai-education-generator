<?php

namespace App\Filament\Student\Resources;

use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use App\Filament\Student\Resources\DocumentResource\Pages;
use App\Filament\Student\Resources\DocumentResource\RelationManagers;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'My Documents';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Document Information')
                    ->description('Add your document details here')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $set('slug', $state ? Str::slug($state) : '');
                            }),
                            
                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                            
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Document')
                            ->disk('public')
                            ->directory('documents')
                            ->preserveFilenames()
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->previewable(true), 

                        Forms\Components\Select::make('document_type')
                            ->options([
                                'pptx' => 'Powerpoint Slides',
                                'pdf' => 'PDF',
                                'docx' => 'Word Document',
                            ])
                            ->required()
                            ->searchable(),

                        Forms\Components\TagsInput::make('tags')
                            ->separator(',')
                            ->suggestions([
                                'math',
                                'science',
                                'history',
                            ])
                            ->helperText('Press Enter or comma to add a tag'),
                    ])
                    ->columns(1), // This makes all fields display in a single column
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn ($record): string => $record->slug ?? ''),
                Tables\Columns\BadgeColumn::make('document_type')
                    ->colors([
                        'warning' => 'pptx',
                        'success' => 'pdf',
                        'info' => 'docx',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TagsColumn::make('tags')
                    ->separator(',')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->multiple()
                    ->options([
                        'pptx' => 'Powerpoint Slides',
                        'pdf' => 'PDF',
                        'docx' => 'Word Document',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('generate')
                        ->icon('heroicon-o-sparkles')
                        ->modalHeading('Generate Content')
                        ->modalDescription('Choose the type of content you want to generate from this document.')
                        ->form([
                            Forms\Components\Select::make('content_type')
                                ->label('Content Type')
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
                        ])
                        ->action(function (array $data): void {
                            // Mock generation action
                            Notification::make()
                                ->title('Content Generation Started')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-document')
            ->emptyStateHeading('No documents yet')
            ->emptyStateDescription('Start by creating your first document.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Document')
                    ->modalWidth('lg')
                    ->modalHeading('Create New Document')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Document created')
                            ->body('Your document has been created successfully.')
                    ),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\QuizzesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'view' => Pages\ViewDocument::route('/{record}'),
        ];
    }
}