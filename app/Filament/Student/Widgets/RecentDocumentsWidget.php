<?php 

namespace App\Filament\Student\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Document;
use Illuminate\Database\Eloquent\Builder;

class RecentDocumentsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Learning Materials')
            ->query(
                Document::whereHas('quizSets', function (Builder $query) {
                    $query->where('user_id', auth()->id());
                })
                ->latest()
                ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('document_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Document $record): string => route('filament.student.resources.documents.view', ['record' => $record])),
            ]);
    }
}