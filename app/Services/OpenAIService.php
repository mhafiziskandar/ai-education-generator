<?php

namespace App\Services;

use OpenAI;
use App\Models\Document;
use App\Models\GeneratedContent;

class OpenAIService
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(config('openai.api_key'));
    }

    public function generateContent(Document $document, string $type): GeneratedContent
    {
        $prompt = $this->getPromptForType($document, $type);
        
        $response = $this->client->chat()->create([
            'model' => config('openai.model'),
            'messages' => [
                ['role' => 'system', 'content' => 'You are an educational content generator assistant.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => config('openai.max_tokens'),
        ]);

        $content = new GeneratedContent();
        $content->document_id = $document->id;
        $content->content_type = $type;
        $content->content = $response->choices[0]->message->content;
        $content->tokens_used = $response->usage->total_tokens;
        $content->save();

        return $content;
    }

    protected function getPromptForType(Document $document, string $type): string
    {
        $basePrompt = "Based on the following educational content:\n\n{$document->content}\n\n";
        
        return match ($type) {
            'quiz' => $basePrompt . "Generate a comprehensive quiz with multiple-choice and short-answer questions.",
            'study_guide' => $basePrompt . "Create a detailed study guide outlining key concepts and learning objectives.",
            'summary' => $basePrompt . "Provide a concise summary of the main topics and important points.",
            'learning_path' => $basePrompt . "Design a structured learning path with recommended study sequence and milestones.",
            default => throw new \InvalidArgumentException("Invalid content type: {$type}"),
        };
    }

    public function estimateTokens(string $text): int
    {
        // Rough estimation: 1 token ≈ 4 characters
        return (int) (strlen($text) / 4);
    }
}