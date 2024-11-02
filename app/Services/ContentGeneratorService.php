<?php

namespace App\Services;

use App\Models\Document;
use App\Models\GeneratedContent;
use OpenAI\Laravel\Facades\OpenAI;

class ContentGeneratorService
{
    // public function generateContent(Document $document, string $contentType): GeneratedContent
    // {
    //     $prompt = $this->getPromptForContentType($document, $contentType);
        
    //     $result = OpenAI::completions()->create([
    //         'model' => 'gpt-3.5-turbo',
    //         'messages' => [
    //             ['role' => 'system', 'content' => 'You are an educational content generator.'],
    //             ['role' => 'user', 'content' => $prompt],
    //         ],
    //     ]);

    //     return GeneratedContent::create([
    //         'document_id' => $document->id,
    //         'content_type' => $contentType,
    //         'content' => $result->choices[0]->message->content,
    //         'tokens_used' => $result->usage->total_tokens,
    //         'api_cost' => $this->calculateCost($result->usage->total_tokens),
    //     ]);
    // }
    public function generateContent(Document $document, string $contentType): GeneratedContent
    {
        // Temporary mock response based on content type
        $content = $this->getMockContent($contentType);
        
        return GeneratedContent::create([
            'document_id' => $document->id,
            'content_type' => $contentType,
            'content' => $content,
            'tokens_used' => 100, // Mock token count
            'api_cost' => 0.002, // Mock cost
        ]);
    }

    private function getMockContent(string $contentType): string
    {
        return match ($contentType) {
            'quiz' => "Sample Quiz Content:\n\n1. Question 1\n2. Question 2\n3. Question 3",
            'summary' => "Sample Summary Content:\nThis is a placeholder summary of the document.",
            'lesson_plan' => "Sample Lesson Plan:\n\nObjectives:\n1. First objective\n2. Second objective",
            default => "Sample generated content for $contentType",
        };
    }

    private function getPromptForContentType(Document $document, string $contentType): string
    {
        return match ($contentType) {
            'quiz' => "Create a comprehensive quiz based on the following educational content:\n\n{$document->content}",
            'summary' => "Create a concise summary of the following educational content:\n\n{$document->content}",
            'lesson_plan' => "Create a structured lesson plan based on the following educational content:\n\n{$document->content}",
            default => throw new \InvalidArgumentException("Invalid content type: {$contentType}"),
        };
    }

    private function calculateCost(int $tokens): float
    {
        // GPT-3.5 Turbo pricing: $0.002 per 1K tokens
        return ($tokens / 1000) * 0.002;
    }
}