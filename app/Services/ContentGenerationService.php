<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use App\Models\GeneratedContent;
use App\Models\Document;
use Smalot\PdfParser\Parser;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Str;

class ContentGenerationService
{
    private const COST_PER_INPUT_TOKEN = 0.0000015;
    private const COST_PER_OUTPUT_TOKEN = 0.000002;
    private const MAX_INPUT_TOKENS = 3000; // Increased for larger input size
    private const MAX_OUTPUT_TOKENS = 500; // Reduced for testing
    private const PDF_PREVIEW_LENGTH = 100;

    private $pdfParser;
    private $dompdf;

    public function __construct()
    {
        // Initialize PDF parser
        $this->pdfParser = new Parser();

        // Configure Dompdf options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $this->dompdf = new Dompdf($options);
    }

    /**
     * Extract content from the given document, handling PDFs specifically.
     */
    private function getDocumentContent(Document $document): string
    {
        if ($document->mime_type === 'application/pdf') {
            try {
                $media = $document->getFirstMedia('documents');
                if (!$media) {
                    \Log::error('PDF file not found in media library', [
                        'document_id' => $document->id,
                    ]);
                    throw new \Exception('PDF file not found in media library');
                }

                $path = $media->getPath();
                \Log::info('PDF processing started.', [
                    'path' => $path,
                    'exists' => file_exists($path),
                    'size' => file_exists($path) ? filesize($path) : 0,
                ]);

                $text = $this->pdfParser->parseFile($path)->getText();
                if (empty(trim($text))) {
                    \Log::error('PDF text extraction returned empty content.', [
                        'path' => $path,
                    ]);
                    throw new \Exception('PDF content is empty or not extractable.');
                }

                \Log::info('PDF content extracted successfully.', [
                    'content_length' => strlen($text),
                    'content_preview' => substr($text, 0, self::PDF_PREVIEW_LENGTH),
                ]);

                return $text;
            } catch (\Exception $e) {
                \Log::error('Error extracting PDF content.', [
                    'error_message' => $e->getMessage(),
                    'stack_trace' => $e->getTraceAsString(),
                ]);
                throw new \Exception('Unable to extract PDF content: ' . $e->getMessage());
            }
        }

        // Return plain content if not a PDF
        $content = $document->content ?? $document->raw_content ?? '';
        \Log::info('Plain document content extracted.', [
            'content_length' => strlen($content),
            'content_preview' => substr($content, 0, self::PDF_PREVIEW_LENGTH),
        ]);

        return $content;
    }

    /**
     * Generate content using OpenAI API and create a PDF.
     */
    public function generateContent(GeneratedContent $generatedContent): string
    {
        $document = $generatedContent->document;
        $generatedContent->update(['status' => 'processing']);

        try {
            $documentContent = $this->getDocumentContent($document);
            if (empty(trim($documentContent))) {
                \Log::error('Document content is empty after extraction.', [
                    'document_id' => $document->id,
                ]);
                $generatedContent->update(['status' => 'failed']);
                throw new \Exception('Document content is empty. Cannot proceed.');
            }

            $documentContent = substr($documentContent, 0, self::MAX_INPUT_TOKENS);

            \Log::info('Document content ready for OpenAI API.', [
                'document_id' => $document->id,
                'content_preview' => substr($documentContent, 0, self::PDF_PREVIEW_LENGTH),
            ]);

            $messages = [
                ['role' => 'system', 'content' => $this->getSystemPrompt($generatedContent->content_type)],
                ['role' => 'user', 'content' => $this->getUserPrompt(
                    $generatedContent->content_type,
                    $documentContent,
                    [
                        'duration_hours' => $generatedContent->duration_hours,
                        'duration_minutes' => $generatedContent->duration_minutes,
                        'grade_level' => $generatedContent->grade_level,
                        'teaching_method' => $generatedContent->teaching_method,
                    ]
                )],
            ];

            \Log::info('Sending request to OpenAI API.', [
                'messages' => $messages,
                'model' => 'gpt-3.5-turbo',
                'max_tokens' => self::MAX_OUTPUT_TOKENS,
            ]);

            // Call OpenAI API
            $result = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => self::MAX_OUTPUT_TOKENS,
            ]);

            $content = $result->choices[0]->message->content;

            \Log::info('OpenAI API response received.', [
                'response_preview' => substr($content, 0, self::PDF_PREVIEW_LENGTH),
                'tokens_used' => $result->usage->total_tokens ?? 'N/A',
            ]);

            // Generate and save PDF
            $html = $this->createPdfHtml($content, $generatedContent->content_type);
            $this->dompdf->loadHtml($html);
            $this->dompdf->setPaper('A4', 'portrait');
            $this->dompdf->render();

            $fileName = Str::slug($generatedContent->content_type) . '_' . now()->timestamp . '.pdf';
            $generatedContent
                ->addMediaFromString($this->dompdf->output())
                ->usingName(Str::slug($generatedContent->content_type))
                ->usingFileName($fileName)
                ->toMediaCollection('generated_pdfs');

            // Update content status
            $generatedContent->update([
                'status' => 'completed',
                'content' => $content,
                'tokens_used' => $result->usage->total_tokens ?? 0,
                'api_cost' => $this->calculateCost($result->usage->total_tokens ?? 0),
            ]);

            \Log::info('Content generation completed successfully.', [
                'generated_content_id' => $generatedContent->id,
            ]);

            return $content;

        } catch (\Exception $e) {
            \Log::error('Content generation error occurred.', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'document_id' => $document->id ?? null,
            ]);

            $generatedContent->update(['status' => 'failed']);
            throw $e;
        }
    }

    /**
     * Generate the HTML for the PDF.
     */
    private function createPdfHtml(string $content, string $contentType): string
    {
        $title = ucfirst(str_replace('_', ' ', $contentType));
        $date = now()->format('F j, Y \a\t g:i A');

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>{$title}</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; color: #333; }
                .header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
                h1 { color: #2c3e50; margin: 0; font-size: 24px; }
                .content { margin: 20px 0; font-size: 14px; }
                .footer { margin-top: 30px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header"><h1>{$title}</h1></div>
            <div class="content">{$content}</div>
            <div class="footer">Generated on: {$date}</div>
        </body>
        </html>
        HTML;
    }

    /**
     * Get system prompt based on content type.
     */
    private function getSystemPrompt(string $contentType): string
    {
        return match ($contentType) {
            'summary' => "You are an educational content summarizer. Create a concise summary within 300 words.",
            'lesson_plan' => "You are an educational lesson plan creator. Create a structured lesson plan within 600 words.",
            'quiz' => "You are an educational quiz creator. Create a set of 5 quiz questions focusing on key concepts.",
            default => throw new \InvalidArgumentException("Invalid content type: {$contentType}")
        };
    }

    /**
     * Get user-specific prompt based on content type and input.
     */
    private function getUserPrompt(string $contentType, string $content, array $options = []): string
    {
        return match ($contentType) {
            'summary' => "Summarize this content in 300 words:\n\n{$content}",
            'lesson_plan' => $this->getLessonPlanPrompt($content, $options),
            'quiz' => "Create 5 questions based on this content:\n\n{$content}",
            default => throw new \InvalidArgumentException("Invalid content type: {$contentType}")
        };
    }

    /**
     * Generate a lesson plan prompt.
     */
    private function getLessonPlanPrompt(string $content, array $options): string
    {
        $duration = ($options['duration_hours'] ?? 1) * 60 + ($options['duration_minutes'] ?? 0);
        $gradeLevel = $options['grade_level'] ?? 'high school';
        $teachingMethod = $options['teaching_method'] ?? 'interactive';

        return "Create a {$duration}-minute lesson plan for {$gradeLevel} level using {$teachingMethod} method. Include:
            1. Objectives
            2. Materials
            3. Introduction
            4. Activities
            5. Assessment
            6. Homework
            Content:\n\n{$content}";
    }

    /**
     * Calculate API usage cost.
     */
    private function calculateCost(int $totalTokens): float
    {
        return round($totalTokens * self::COST_PER_OUTPUT_TOKEN, 4);
    }
}