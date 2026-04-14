<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeBase extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'content',
        'tags',
        'is_active',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public const CATEGORY_COMPANY = 'company';
    public const CATEGORY_ACCOUNT = 'account';
    public const CATEGORY_RESOURCE = 'resource';
    public const CATEGORY_FAQ = 'faq';
    public const CATEGORY_SCRIPT = 'script';
    public const CATEGORY_POLICY = 'policy';

    public static function categories(): array
    {
        return [
            self::CATEGORY_COMPANY => 'Company Info',
            self::CATEGORY_ACCOUNT => 'Account Details',
            self::CATEGORY_RESOURCE => 'Resources',
            self::CATEGORY_FAQ => 'FAQ',
            self::CATEGORY_SCRIPT => 'Scripts',
            self::CATEGORY_POLICY => 'Policies',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(KnowledgeBaseEntry::class)->orderBy('chunk_index');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSearch($query, string $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->where('title', 'LIKE', "%{$searchTerm}%")
              ->orWhere('description', 'LIKE', "%{$searchTerm}%")
              ->orWhere('content', 'LIKE', "%{$searchTerm}%");
        });
    }

    public function searchContent(string $query): \Illuminate\Database\Eloquent\Collection
    {
        return $this->entries()
            ->where('chunk', 'LIKE', "%{$query}%")
            ->get();
    }

    public function createChunks(int $chunkSize = 500): void
    {
        $this->entries()->delete();

        if (empty($this->content)) {
            return;
        }

        $chunks = $this->splitIntoChunks($this->content, $chunkSize);

        foreach ($chunks as $index => $chunk) {
            $this->entries()->create([
                'chunk' => $chunk,
                'chunk_index' => $index,
            ]);
        }
    }

    protected function splitIntoChunks(string $text, int $size): array
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', $text);
        $chunks = [];
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            if (strlen($currentChunk . $sentence) <= $size) {
                $currentChunk .= ($currentChunk ? ' ' : '') . $sentence;
            } else {
                if ($currentChunk) {
                    $chunks[] = trim($currentChunk);
                }
                $currentChunk = $sentence;
            }
        }

        if ($currentChunk) {
            $chunks[] = trim($currentChunk);
        }

        return $chunks;
    }
}
