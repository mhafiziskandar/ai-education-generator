<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Document extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'file_path',
        'content',
        'status',
        'file_type',
        'token_count',
        'processing_status',
    ];

    public function generatedContents(): HasMany
    {
        return $this->hasMany(GeneratedContent::class);
    }
}