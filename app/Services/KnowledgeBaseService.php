<?php

namespace App\Services;

use App\Models\KnowledgeBase;
use App\Models\KnowledgeBaseEntry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class KnowledgeBaseService
{
    public function search(string $query, ?string $category = null, int $limit = 10): Collection
    {
        $queryBuilder = KnowledgeBase::active()
            ->where(function($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('content', 'LIKE', "%{$query}%");
            });

        if ($category) {
            $queryBuilder->where('category', $category);
        }

        $results = $queryBuilder->limit($limit)->get();

        $resultsWithChunks = $results->map(function ($kb) use ($query) {
            $matchingChunks = $kb->entries()
                ->where('chunk', 'LIKE', "%{$query}%")
                ->limit(3)
                ->get();

            $kb->matching_chunks = $matchingChunks;
            $kb->match_score = $this->calculateRelevance($kb, $query);
            
            return $kb;
        });

        return $resultsWithChunks->sortByDesc('match_score')->take($limit);
    }

    public function getContextForAI(string $transcript, int $maxChunks = 5): string
    {
        $contextParts = [];
        $keywords = $this->extractKeywords($transcript);

        foreach ($keywords as $keyword) {
            $entries = KnowledgeBaseEntry::where('chunk', 'LIKE', "%{$keyword}%")
                ->with('knowledgeBase')
                ->limit(2)
                ->get();

            foreach ($entries as $entry) {
                if (!in_array($entry->chunk, $contextParts)) {
                    $contextParts[] = "[{$entry->knowledgeBase->category}] {$entry->chunk}";
                }
            }
        }

        if (empty($contextParts)) {
            $entries = KnowledgeBaseEntry::with('knowledgeBase')
                ->whereHas('knowledgeBase', fn($q) => $q->where('is_active', true))
                ->inRandomOrder()
                ->limit($maxChunks)
                ->get();

            foreach ($entries as $entry) {
                $contextParts[] = "[{$entry->knowledgeBase->category}] {$entry->chunk}";
            }
        }

        return implode("\n\n", array_slice($contextParts, 0, $maxChunks));
    }

    public function getRelevantEntries(string $query, int $limit = 5): Collection
    {
        return KnowledgeBaseEntry::with('knowledgeBase')
            ->whereHas('knowledgeBase', fn($q) => $q->where('is_active', true))
            ->where('chunk', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    public function createOrUpdate(array $data): KnowledgeBase
    {
        $data['user_id'] = $data['user_id'] ?? (Auth::check() ? Auth::id() : null);

        if (isset($data['id']) && $data['id']) {
            $kb = KnowledgeBase::find($data['id']);
            if ($kb) {
                $kb->update([
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'category' => $data['category'] ?? 'company',
                    'content' => $data['content'] ?? '',
                    'tags' => $data['tags'] ?? [],
                    'is_active' => $data['is_active'] ?? true,
                ]);
            }
        } else {
            $kb = KnowledgeBase::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? 'company',
                'content' => $data['content'] ?? '',
                'tags' => $data['tags'] ?? [],
                'is_active' => $data['is_active'] ?? true,
                'user_id' => $data['user_id'],
            ]);
        }

        if (isset($data['content'])) {
            $kb->createChunks();
        }

        return $kb;
    }

    protected function calculateRelevance(KnowledgeBase $kb, string $query): float
    {
        $score = 0;
        $queryLower = strtolower($query);

        if (stripos($kb->title, $query) !== false) {
            $score += 10;
        }

        if (stripos($kb->description, $query) !== false) {
            $score += 5;
        }

        if (stripos($kb->content, $query) !== false) {
            $score += 3;
        }

        $tags = $kb->tags ?? [];
        foreach ($tags as $tag) {
            if (stripos($tag, $query) !== false) {
                $score += 2;
            }
        }

        return $score;
    }

    protected function extractKeywords(string $text): array
    {
        $text = strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        $words = str_word_count($text, 1);

        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'this', 'that', 'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'what', 'which', 'who', 'when', 'where', 'why', 'how'];

        $keywords = array_filter($words, fn($word) => strlen($word) > 3 && !in_array($word, $stopWords));

        $wordCounts = array_count_values($keywords);
        arsort($wordCounts);

        return array_slice(array_keys($wordCounts), 0, 10);
    }
}
