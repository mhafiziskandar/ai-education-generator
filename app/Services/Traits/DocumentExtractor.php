<?php

namespace App\Services\Traits;

use Spatie\PdfToText\Pdf;
use PhpOffice\PhpWord\IOFactory as WordFactory;
use PhpOffice\PhpPresentation\IOFactory as PresentationFactory;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

trait DocumentExtractor
{
    /**
     * Extract content from PDF using multiple methods with fallback.
     */
    private function extractPdfContent(string $path): string
    {
        \Log::info('Starting PDF extraction', [
            'path' => $path,
            'exists' => file_exists($path),
            'size' => file_exists($path) ? filesize($path) : 0
        ]);

        // Method 1: Try pdftotext (Spatie)
        try {
            $text = (new Pdf())
                ->setPdf($path)
                ->text();

            if (!empty(trim($text))) {
                \Log::info('PDF extracted successfully using pdftotext', [
                    'length' => strlen($text)
                ]);
                return $text;
            }
        } catch (\Exception $e) {
            \Log::warning('Spatie PDF extraction failed', ['error' => $e->getMessage()]);
        }

        // Method 2: Try direct pdftotext command
        try {
            $result = Process::run("pdftotext -layout '{$path}' -");
            if ($result->successful()) {
                $text = $result->output();
                if (!empty(trim($text))) {
                    \Log::info('PDF extracted successfully using pdftotext command', [
                        'length' => strlen($text)
                    ]);
                    return $text;
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Command line PDF extraction failed', ['error' => $e->getMessage()]);
        }

        // Method 3: Try Smalot Parser
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $text = $parser->parseFile($path)->getText();

            if (!empty(trim($text))) {
                \Log::info('PDF extracted successfully using Smalot', [
                    'length' => strlen($text)
                ]);
                return $text;
            }
        } catch (\Exception $e) {
            \Log::warning('Smalot PDF extraction failed', ['error' => $e->getMessage()]);
        }

        // Method 4: OCR as last resort for scanned PDFs
        try {
            $imagePaths = $this->convertPdfToImages($path);
            $text = '';
            
            foreach ($imagePaths as $imagePath) {
                $result = Process::run("tesseract '{$imagePath}' stdout");
                if ($result->successful()) {
                    $text .= $result->output();
                }
                unlink($imagePath); // Clean up
            }

            if (!empty(trim($text))) {
                \Log::info('PDF extracted successfully using OCR', [
                    'length' => strlen($text)
                ]);
                return $text;
            }
        } catch (\Exception $e) {
            \Log::warning('OCR extraction failed', ['error' => $e->getMessage()]);
        }

        throw new \Exception('Unable to extract PDF content using any available method');
    }

    /**
     * Convert PDF pages to images for OCR processing.
     */
    private function convertPdfToImages(string $pdfPath): array
    {
        $tempDir = Storage::path('temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $imagePaths = [];
        
        try {
            // Using magick command for ImageMagick 7
            $density = 300;
            $quality = 100;
            $outputPattern = $tempDir . '/page_%d.png';
            
            Process::run("magick -density {$density} '{$pdfPath}' -quality {$quality} '{$outputPattern}'");
            
            foreach (glob($tempDir . '/page_*.png') as $imagePath) {
                $imagePaths[] = $imagePath;
            }
            
            return $imagePaths;
        } catch (\Exception $e) {
            // Clean up any partially created files
            foreach ($imagePaths as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            throw $e;
        }
    }

    /**
     * Extract content from DOCX files.
     */
    private function extractDocxContent(string $path): string
    {
        \Log::info('Starting DOCX extraction', [
            'path' => $path,
            'exists' => file_exists($path),
            'size' => file_exists($path) ? filesize($path) : 0
        ]);

        // Validate file
        if (!file_exists($path)) {
            throw new \Exception("DOCX file does not exist: {$path}");
        }

        if (filesize($path) === 0) {
            throw new \Exception("DOCX file is empty: {$path}");
        }

        try {
            // Try ZipArchive first to verify DOCX structure
            $zip = new \ZipArchive();
            if ($zip->open($path) !== true) {
                throw new \Exception("Unable to open DOCX file as ZIP archive");
            }

            // Verify essential DOCX components
            $requiredFiles = ['word/document.xml', '[Content_Types].xml'];
            foreach ($requiredFiles as $file) {
                if ($zip->locateName($file) === false) {
                    $zip->close();
                    throw new \Exception("Invalid DOCX structure: missing {$file}");
                }
            }
            $zip->close();

            // Now try loading with PHPWord
            $phpWord = WordFactory::load($path);
            $text = '';
            
            foreach ($phpWord->getSections() as $section) {
                // Process each section
                foreach ($section->getElements() as $element) {
                    // Direct text elements
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                        continue;
                    }
                    
                    // Tables
                    if (method_exists($element, 'getRows')) {
                        foreach ($element->getRows() as $row) {
                            foreach ($row->getCells() as $cell) {
                                foreach ($cell->getElements() as $cellElement) {
                                    if (method_exists($cellElement, 'getText')) {
                                        $text .= $cellElement->getText() . " ";
                                    }
                                }
                            }
                            $text .= "\n";
                        }
                        continue;
                    }
                    
                    // Nested elements (like TextRun)
                    if (method_exists($element, 'getElements')) {
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

        } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
            \Log::error('PHPWord extraction failed', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);
            throw new \Exception('PHPWord extraction failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('DOCX extraction failed', [
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
        \Log::info('Starting PPTX extraction', [
            'path' => $path,
            'exists' => file_exists($path)
        ]);

        try {
            $presentation = PresentationFactory::load($path);
            $text = '';
            
            foreach ($presentation->getAllSlides() as $slideIndex => $slide) {
                $text .= "Slide " . ($slideIndex + 1) . ":\n";
                
                // Get slide title
                if ($slide->getTitle()) {
                    $text .= "Title: " . $slide->getTitle() . "\n";
                }
                
                // Extract text from shapes
                foreach ($slide->getShapeCollection() as $shape) {
                    if (method_exists($shape, 'getText')) {
                        $text .= $shape->getText() . "\n";
                    }
                }
                
                $text .= "\n";
            }

            $text = trim($text);
            if (empty($text)) {
                throw new \Exception('Extracted PPTX content is empty');
            }

            return $text;
        } catch (\Exception $e) {
            \Log::error('PPTX extraction failed', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Unable to extract PPTX content: ' . $e->getMessage());
        }
    }
}