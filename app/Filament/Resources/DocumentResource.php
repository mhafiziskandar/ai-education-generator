<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers;
use App\Models\Document;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Document')
                            ->required()
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                            ])
                            ->directory('documents')
                            ->preserveFilenames(),
                        Forms\Components\Select::make('category')
                            ->options([
                                'presentation' => 'Presentation Slides',
                                'document' => 'Document',
                                'worksheet' => 'Worksheet',
                            ])
                            ->required(),
                        Forms\Components\TagsInput::make('tags')
                            ->separator(',')
                            ->suggestions([
                                'math',
                                'science',
                                'history',
                                'literature'
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TagsColumn::make('tags'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'presentation' => 'Presentation Slides',
                        'document' => 'Document',
                        'worksheet' => 'Worksheet',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('generate')
                    ->form([
                        Forms\Components\Select::make('content_type')
                            ->label('Generate Content Type')
                            ->options([
                                'quiz' => 'Quiz Questions',
                                'study_guide' => 'Study Guide',
                                'summary' => 'Chapter Summary',
                                'learning_path' => 'Learning Path',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('token_count')
                            ->label('Token Count')
                            ->numeric()
                            ->default(500),
                        Forms\Components\Toggle::make('include_images')
                            ->label('Include Images')
                            ->default(true),
                    ])
                    ->action(function (Model $record, array $data): void {
                        static::generateContent($record, $data);
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-document') // Optional: Set an icon for empty state
            ->emptyStateHeading('No documents found') // Heading when there's no data
            ->emptyStateDescription('Upload a document or generate content to get started.') // Description when there's no data
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(), // Option to create a new document when table is empty
            ]);
    }

    protected static function generateContent(Model $record, array $data): void
    {
        // Implement your AI content generation logic here
        $generatedContent = new \App\Models\GeneratedContent();
        $generatedContent->document_id = $record->id;
        $generatedContent->content_type = $data['content_type'];
        $generatedContent->settings = [
            'token_count' => $data['token_count'],
            'include_images' => $data['include_images'],
        ];
        // Add AI processing here
        $generatedContent->save();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\GeneratedContentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}