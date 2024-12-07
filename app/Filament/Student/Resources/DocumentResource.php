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
use App\Filament\Student\Resources\DocumentResource\Pages;
use App\Filament\Student\Resources\DocumentResource\RelationManagers\QuizSetsRelationManager;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Set;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'My Documents';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id()); // Changed to filter by user_id directly
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Document Information')
                    ->description('Upload your document here')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (string $state, Set $set) {
                                $set('slug', Str::slug($state));
                            }),
                            
                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated(),
                        
                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id()),
                            
                        SpatieMediaLibraryFileUpload::make('document')
                            ->collection('document')
                            ->preserveFilenames()
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->previewable()
                            ->required(),

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
                    ->columns(1),
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

                Tables\Columns\TextColumn::make('document_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pptx' => 'warning',
                        'pdf' => 'success',
                        'docx' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tags')
                    ->badge()
                    ->separator(',')
                    ->toggleable(),
            ])
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-document')
            ->emptyStateHeading('No documents yet')
            ->emptyStateDescription('Start by uploading your first document.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Upload Document'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            QuizSetsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            // 'edit' => Pages\EditDocument::route('/{record}/edit'),
            'view' => Pages\ViewDocument::route('/{record}'),
        ];
    }
}