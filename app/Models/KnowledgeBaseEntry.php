<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeBaseEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'knowledge_base_id',
        'chunk',
        'chunk_index',
    ];

    protected function casts(): array
    {
        return [
            'chunk_index' => 'integer',
        ];
    }

    public function knowledgeBase(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBase::class);
    }
}
