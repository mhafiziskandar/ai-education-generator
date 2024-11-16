<?php

namespace App\Filament\Student\Resources\QuizSetResource\Pages;

use App\Filament\Student\Resources\QuizSetResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;
use Livewire\Attributes\Computed;

class TakeQuizSet extends Page
{
    use InteractsWithRecord;

    protected static string $resource = QuizSetResource::class;
    protected static string $view = 'filament/student/resources/quiz-set/pages/take-quiz';
    
    public $answers = [];
    
    public function mount(string $record): void
    {
        static::authorizeResourceAccess();
        $this->record = $this->resolveRecord($record);
        
        // Initialize answers array
        foreach ($this->record->questions as $question) {
            $this->answers[$question->id] = null;
        }
    }

    #[Computed]
    public function totalQuestions(): int
    {
        return $this->record->questions->count();
    }

    public function submit(): void
    {
        // Validate that all questions are answered
        $this->validate([
            'answers.*' => 'required',
        ], [
            'answers.*.required' => 'Please answer all questions before submitting.',
        ]);

        // Save answers logic here
        // ...

        Notification::make()
            ->title('Quiz submitted successfully')
            ->success()
            ->send();

        $this->redirect(static::getResource()::getUrl('index'));
    }
}