<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'file_path',
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
            // Delete all related quiz sets first (this will cascade to quizzes if you set up the migrations)
            $document->quizSets()->each(function ($quizSet) {
                $quizSet->questions()->delete();  // Delete related quizzes first
                $quizSet->delete();  // Then delete the quiz set
            });
            
            // Delete the file if it exists
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generatedContents(): HasMany
    {
        return $this->hasMany(GeneratedContent::class);
    }

    public function quizSets(): HasMany
    {
        return $this->hasMany(QuizSet::class);
    }

    public function getFileUrlAttribute()
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }
}