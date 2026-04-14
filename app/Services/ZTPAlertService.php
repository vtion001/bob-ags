<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ZTPAlertService
{
    protected array $ztpPatterns = [
        [
            'category' => 'medical_advice',
            'keywords' => ['doctor', 'prescription', 'diagnosis', 'medical', 'medication dosage', 'treatment plan'],
            'severity' => 'high',
            'message' => 'The agent may be providing medical advice. Agents should not diagnose or prescribe.',
        ],
        [
            'category' => 'hipaa_risk',
            'keywords' => ['my doctor said', 'my physician', 'prescribed', 'diagnosis'],
            'severity' => 'medium',
            'message' => 'The agent mentioned medical information. Ensure no protected health information is discussed.',
        ],
        [
            'category' => 'unqualified_transfer',
            'keywords' => ['insurance', 'va benefits', 'kaiser', 'out of state', 'self-pay'],
            'severity' => 'low',
            'message' => 'Qualification issue detected. Verify caller eligibility before transfer.',
        ],
        [
            'category' => 'crisis_indicator',
            'keywords' => ['kill myself', 'end it all', 'suicide', 'overdose', 'die'],
            'severity' => 'critical',
            'message' => 'Crisis language detected. Follow emergency protocols immediately.',
        ],
        [
            'category' => 'safety_concern',
            'keywords' => ['harm myself', 'hurt myself', 'self-harm', 'cutting'],
            'severity' => 'high',
            'message' => 'Self-harm language detected. Assess for immediate safety concerns.',
        ],
        [
            'category' => 'compliance',
            'keywords' => ['guarantee', 'promise', 'certain', 'definitely'],
            'severity' => 'low',
            'message' => 'Language may indicate overpromising. Avoid guarantees.',
        ],
        [
            'category' => 'scope',
            'keywords' => ['legal advice', 'attorney', 'lawsuit', 'court'],
            'severity' => 'medium',
            'message' => 'Legal question detected. Redirect to appropriate legal resources.',
        ],
        [
            'category' => 'information_security',
            'keywords' => ['social security', 'ssn', 'credit card', 'bank account'],
            'severity' => 'high',
            'message' => 'Sensitive information discussion. Remind caller about privacy protocols.',
        ],
    ];

    public function checkForZTPViolation(string $text, string $fullTranscript = ''): ?array
    {
        $textLower = strtolower($text);
        $fullLower = strtolower($fullTranscript);

        foreach ($this->ztpPatterns as $pattern) {
            foreach ($pattern['keywords'] as $keyword) {
                $keywordLower = strtolower($keyword);

                if (strpos($textLower, $keywordLower) !== false) {
                    $alert = [
                        'category' => $pattern['category'],
                        'keyword' => $keyword,
                        'severity' => $pattern['severity'],
                        'message' => $pattern['message'],
                        'matched_text' => $this->extractContext($text, $keyword),
                    ];

                    Log::info('ZTP Alert triggered', $alert);

                    return $alert;
                }
            }
        }

        return null;
    }

    public function getSeverityLevel(string $severity): int
    {
        return match($severity) {
            'critical' => 5,
            'high' => 4,
            'medium' => 3,
            'low' => 2,
            default => 1,
        };
    }

    public function getAllPatterns(): array
    {
        return $this->ztpPatterns;
    }

    public function getPatternsByCategory(string $category): array
    {
        return array_filter($this->ztpPatterns, fn($p) => $p['category'] === $category);
    }

    protected function extractContext(string $text, string $keyword, int $contextLength = 50): string
    {
        $textLower = strtolower($text);
        $keywordLower = strtolower($keyword);
        $pos = strpos($textLower, $keywordLower);

        if ($pos === false) {
            return $text;
        }

        $start = max(0, $pos - $contextLength);
        $end = min(strlen($text), $pos + strlen($keyword) + $contextLength);

        $context = substr($text, $start, $end - $start);

        if ($start > 0) {
            $context = '...' . $context;
        }
        if ($end < strlen($text)) {
            $context = $context . '...';
        }

        return $context;
    }
}
