<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use App\Models\GeneratedContent;
use App\Models\Document;
use App\Services\Traits\DocumentExtractor;
use Smalot\PdfParser\Parser;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Str;
use Spatie\PdfToText\Pdf;
use PhpOffice\PhpWord\IOFactory as WordFactory;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpPresentation\IOFactory as PresentationFactory;

class ContentGenerationService
{
    use DocumentExtractor;

    private const COST_PER_INPUT_TOKEN = 0.0000015;
    private const COST_PER_OUTPUT_TOKEN = 0.000002;
    private const MAX_INPUT_TOKENS = 3000; // Increased for larger input size
    private const MAX_OUTPUT_TOKENS = 500; // Reduced for testing
    private const PDF_PREVIEW_LENGTH = 100;
    private const DOCUMENT_COLLECTION = 'document';
    private const GENERATED_PDF_COLLECTION = 'generated_pdfs';

    private $pdfParser;
    private $dompdf;

    public function __construct()
    {
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
        \Log::info('Accessing document', [
            'document_id' => $document->id,
            'mime_type' => $document->mime_type,
            'media_collections' => $document->getMedia()->pluck('collection_name'),
            'has_document_collection' => $document->getMedia('document')->count(),
            'first_media' => $document->getFirstMedia('document') ? 'exists' : 'null',
        ]);
        
        $media = $document->getFirstMedia('document');
        if (!$media) {
            throw new \Exception('Document file not found in media library');
        }

        $path = $media->getPath();
        if (!file_exists($path)) {
            throw new \Exception("File does not exist at path: {$path}");
        }

        $mimeType = $document->mime_type ?? $media->mime_type;
        if (!$mimeType) {
            throw new \Exception('MIME type is not set for document');
        }

        \Log::info('Processing document', [
            'mime_type' => $mimeType,
            'path' => $path,
            'file_size' => filesize($path)
        ]);

        try {
            return match ($mimeType) {
                'application/pdf' => $this->extractPdfContent($path),
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword' => $this->extractDocxContent($path),
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.ms-powerpoint' => $this->extractPptxContent($path),
                default => throw new \Exception("Unsupported file type: {$mimeType}")
            };
        } catch (\Exception $e) {
            \Log::error("Error extracting content from {$mimeType} document", [
                'error' => $e->getMessage(),
                'document_id' => $document->id,
                'path' => $path
            ]);
            throw $e;
        }
    }

    /**
     * Extract content from PDF using multiple fallback methods.
     */
    private function extractPdfContent(string $path): string
    {
        // Try pdftotext first (requires poppler-utils)
        try {
            $text = (new Pdf())
                ->setPdf($path)
                ->setOptions(['layout', 'quiet'])
                ->text();

            if (!empty(trim($text))) {
                return $text;
            }
        } catch (\Exception $e) {
            \Log::warning('pdftotext extraction failed, trying OCR fallback', [
                'error' => $e->getMessage()
            ]);
        }

        // Fallback to Tesseract OCR for scanned PDFs
        try {
            // Convert PDF to images first
            $imagePaths = $this->convertPdfToImages($path);
            $text = '';
            
            foreach ($imagePaths as $imagePath) {
                $text .= (new \thiagoalessio\TesseractOCR\TesseractOCR($imagePath))
                    ->run();
                unlink($imagePath); // Clean up temporary image
            }

            if (!empty(trim($text))) {
                return $text;
            }
        } catch (\Exception $e) {
            \Log::warning('OCR extraction failed', [
                'error' => $e->getMessage()
            ]);
        }

        // Last resort: try Smalot PDF Parser
        try {
            $parser = new \Smalot\PdfParser\Parser();
            return $parser->parseFile($path)->getText();
        } catch (\Exception $e) {
            \Log::error('All PDF extraction methods failed', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Unable to extract PDF content using any available method');
        }
    }

    /**
     * Convert PDF pages to images for OCR processing.
     */
    private function convertPdfToImages(string $pdfPath): array
    {
        $imagePaths = [];
        $imagick = new \Imagick();
        $imagick->setResolution(300, 300);
        $imagick->readImage($pdfPath);
        
        foreach ($imagick as $i => $page) {
            $page->setImageFormat('png');
            $imagePath = storage_path("app/temp/page_{$i}.png");
            $page->writeImage($imagePath);
            $imagePaths[] = $imagePath;
        }

        return $imagePaths;
    }

    /**
     * Extract content from DOCX files.
     */
    private function extractDocxContent(string $path): string
    {
        try {
            \Log::info('Starting DOCX extraction', [
                'path' => $path,
                'file_exists' => file_exists($path),
            ]);

            $phpWord = WordFactory::load($path);
            $text = '';
            
            // Extract text from each section
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    } elseif (method_exists($element, 'getElements')) {
                        // Handle tables and other complex elements
                        foreach ($element->getElements() as $childElement) {
                            if (method_exists($childElement, 'getText')) {
                                $text .= $childElement->getText() . "\n";
                            }
                        }
                    }
                }
            }

            $text = trim($text);
            
            if (empty($text)) {
                \Log::warning('DOCX extraction produced empty text', [
                    'path' => $path
                ]);
                throw new \Exception('Extracted DOCX content is empty');
            }

            \Log::info('Successfully extracted DOCX content', [
                'text_length' => strlen($text),
                'preview' => substr($text, 0, 100)
            ]);

            return $text;
        } catch (\Exception $e) {
            \Log::error('Failed to extract DOCX content', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);
            throw new \Exception('Unable to extract DOCX content: ' . $e->getMessage());
        }
    }

    /**
     * Extract content from PPTX files.
     */
    private function extractPptxContent(string $path): string
    {
        $presentation = PresentationFactory::load($path);
        $text = '';
        
        foreach ($presentation->getAllSlides() as $slide) {
            foreach ($slide->getShapeCollection() as $shape) {
                if (method_exists($shape, 'getText')) {
                    $text .= $shape->getText() . "\n";
                }
            }
        }
        
        return $text;
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