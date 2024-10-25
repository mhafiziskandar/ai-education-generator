<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GeneratedContent extends Model
{
    use HasUuids;

    protected $fillable = [
        'document_id',
        'content_type', // quiz, study_guide, summary, learning_path
        'content',
        'status',
        'token_count',
        'api_cost',
    ];

    protected $casts = [
        'api_cost' => 'decimal:4',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}