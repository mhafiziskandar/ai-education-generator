<?php

namespace App\Filament\Student\Resources\QuizSetResource\Pages;

use App\Filament\Student\Resources\QuizSetResource;
use Filament\Resources\Pages\Page;
use App\Models\QuizSet;

class ViewQuizResult extends Page
{
    protected static string $resource = QuizSetResource::class;

    protected static string $view = 'filament.student.resources.quiz-set-resource.pages.view-quiz-result';
    
    public QuizSet $record;

    public function mount(QuizSet $record): void 
    {
        $this->record = $record;
    }
}