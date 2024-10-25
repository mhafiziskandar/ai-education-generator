<?php

namespace App\Services;

use App\Models\Document;
use App\Models\GeneratedContent;
use OpenAI\Laravel\Facades\OpenAI;

class ContentGenerationService
{
    public function generateContent(Document $document, string $contentType): GeneratedContent
    {
        $prompt = $this->getPromptForContentType($document, $contentType);
        
        $result = OpenAI::completions()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are an educational content generator.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return GeneratedContent::create([
            'document_id' => $document->id,
            'content_type' => $contentType,
            'content' => $result->choices[0]->message->content,
            'token_count' => $result->usage->total_tokens,
            'api_cost' => $this->calculateCost($result->usage->total_tokens),
        ]);
    }

    private function getPromptForContentType(Document $document, string $contentType): string
    {
        return match ($contentType) {
            'quiz' => "Create a comprehensive quiz based on the following educational content:\n\n{$document->content}",
            'study_guide' => "Create a detailed study guide based on the following educational content:\n\n{$document->content}",
            'summary' => "Create a concise summary of the following educational content:\n\n{$document->content}",
            'learning_path' => "Create a structured learning path based on the following educational content:\n\n{$document->content}",
            default => throw new \InvalidArgumentException("Invalid content type: {$contentType}"),
        };
    }

    private function calculateCost(int $tokens): float
    {
        // GPT-3.5 Turbo pricing: $0.002 per 1K tokens
        return ($tokens / 1000) * 0.002;
    }
}