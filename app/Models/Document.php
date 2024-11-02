<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Document extends Model
{
    use HasFactory;
    // use HasUuids;

    protected $fillable = [
        'title',
        'file_path',
        'document_type',
        'content',
        'tokens_used',
        'processing_status',
    ];

    protected $cast = [
        'tags' => 'array',
        'file_path' => 'array'
    ];

    protected static function boot() {
        parent::boot();

        static::deleting(function ($document) {
            if($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }
        });
    }

    public function generatedContents(): HasMany
    {
        return $this->hasMany(GeneratedContent::class);
    }

    public function getFileUrlAttribute()
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }
}