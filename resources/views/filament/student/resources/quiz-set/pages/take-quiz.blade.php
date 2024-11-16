<x-filament-panels::page>
    <form wire:submit="submit">
        <div class="space-y-6">
            {{-- Header Section --}}
            <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-950/5 p-4 md:p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ $this->record->document->title }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Total Questions: {{ $this->totalQuestions() }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Questions Section --}}
            <div class="space-y-4">
                @foreach($this->record->questions as $index => $question)
                    <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-950/5 p-4 md:p-6">
                        <div class="space-y-4">
                            {{-- Question --}}
                            <div class="flex items-start gap-x-3">
                                <div class="flex-none">
                                    <span class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-2.5 py-0.5 text-gray-900">
                                        {{ $index + 1 }}
                                    </span>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-base font-medium text-gray-900">
                                        {{ $question->question }}
                                    </h4>
                                </div>
                            </div>

                            {{-- Answer Options --}}
                            <div class="pl-10 space-y-3">
                                <label class="flex items-center gap-x-3">
                                    <input
                                        type="radio"
                                        wire:model="answers.{{ $question->id }}"
                                        name="question_{{ $question->id }}"
                                        value="{{ $question->correct_answer }}"
                                        class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-600"
                                    >
                                    <span class="text-sm text-gray-700">
                                        {{ $question->correct_answer }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Submit Button Section --}}
            <div class="flex justify-end gap-x-3 bg-white rounded-lg shadow-sm ring-1 ring-gray-950/5 p-4 md:p-6">
                <x-filament::button
                    type="submit"
                    size="lg"
                >
                    Submit Quiz
                </x-filament::button>
            </div>
        </div>
    </form>
</x-filament-panels::page>