<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class GeneratedContent extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'document_id',
        'user_id',
        'content_type',
        'content',
        'status',
        'tokens_used',
        'api_cost',
        'duration_hours',
        'duration_minutes',
        'grade_level',
        'teaching_method'
    ];

    protected $casts = [
        'api_cost' => 'decimal:4',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('generated_pdfs')
            ->useDisk('public')
            ->singleFile();     // Only keep the latest PDF
    }

    public function registerMediaConversions(Media $media = null): void
    {
        
    }

    public function getPdfUrl(): ?string
    {
        $media = $this->getFirstMedia('generated_pdfs');
        return $media ? $media->getUrl() : null;
    }

    public function getPdfPath(): ?string
    {
        $media = $this->getFirstMedia('generated_pdfs');
        return $media ? $media->getPath() : null;
    }
}