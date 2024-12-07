<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'document_type',
        'tags',
        'tokens_used',
        'processing_status'
    ];

    protected $casts = [
        'tags' => 'array',
        'tokens_used' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($document) {
            $document->quizSets()->delete();
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('document')
            ->singleFile()
            ->acceptsMimeTypes(['application/pdf', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quizSets(): HasMany
    {
        return $this->hasMany(QuizSet::class);
    }

    public function quizzes()
    {
        return $this->hasManyThrough(Quiz::class, QuizSet::class);
    }

    public function generatedContents(): HasMany
    {
        return $this->hasMany(GeneratedContent::class);
    }
}