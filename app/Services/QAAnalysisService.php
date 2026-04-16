<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class QAAnalysisService
{
    protected OpenAIService $openRouter;

    protected array $rubric;

    protected array $criteria;

    protected array $ztpCriteria;

    protected array $alwaysNaCriteria;

    public function __construct(OpenAIService $openRouter)
    {
        $this->openRouter = $openRouter;
        $this->rubric = config('qarubric');
        $this->criteria = $this->rubric['criteria'];
        $this->ztpCriteria = $this->rubric['ztp_criteria'];
        $this->alwaysNaCriteria = $this->rubric['always_na_criteria'];
    }

    public function analyzeTranscript(string $callId, string $transcriptText): array
    {
        $results = [
            'call_id' => $callId,
            'score' => 0,
            'sentiment' => 'neutral',
            'summary' => '',
            'tags' => [],
            'disposition' => '',
            'rubric_results' => [],
            'rubric_breakdown' => [],
            'ztp_violations' => [],
            'total_criteria' => 25,
            'passed_criteria' => 0,
            'failed_criteria' => 0,
            'na_criteria' => 0,
        ];

        if (empty(trim($transcriptText))) {
            return $results;
        }

        $transcriptTextLower = strtolower($transcriptText);

        $aiResults = $this->analyzeWithAI($transcriptText);

        foreach ($this->criteria as $id => $criterion) {
            $isNa = $criterion['na'] || in_array($id, $this->alwaysNaCriteria);

            if ($isNa) {
                $results['rubric_results'][$id] = [
                    'id' => $id,
                    'criterion' => $criterion['name'],
                    'category' => $criterion['category'],
                    'pass' => true,
                    'na' => true,
                    'ztp' => $criterion['ztp'],
                    'details' => 'N/A - Requires manual verification',
                    'deduction' => 0,
                    'severity' => $criterion['severity'],
                    'points' => $criterion['points'],
                ];
                $results['na_criteria']++;

                continue;
            }

            $pass = false;
            $details = '';

            if (isset($aiResults[$id])) {
                $pass = $aiResults[$id]['pass'];
                $details = $aiResults[$id]['details'] ?? '';
            } else {
                $keywordResult = $this->keywordMatch($id, $transcriptTextLower, $criterion);
                $pass = $keywordResult['pass'];
                $details = $keywordResult['details'];
            }

            $deduction = $pass ? 0 : $criterion['points'];

            if ($criterion['ztp'] && ! $pass) {
                $deduction = 0;
                $results['ztp_violations'][] = $id;
            }

            $results['rubric_results'][$id] = [
                'id' => $id,
                'criterion' => $criterion['name'],
                'category' => $criterion['category'],
                'pass' => $pass,
                'na' => false,
                'ztp' => $criterion['ztp'],
                'autoFail' => $criterion['ztp'] && ! $pass,
                'details' => $details,
                'deduction' => $deduction,
                'severity' => $criterion['severity'],
                'points' => $criterion['points'],
            ];

            if ($pass) {
                $results['passed_criteria']++;
            } else {
                $results['failed_criteria']++;
            }
        }

        $results = $this->calculateBreakdown($results);
        $results = $this->calculateScore($results);
        $results = $this->generateTags($results);
        $results = $this->generateSummary($results);
        $results = $this->getDisposition($results);

        return $results;
    }

    protected function analyzeWithAI(string $transcriptText)
    {
        return $this->openRouter->analyzeCall($transcriptText, $this->criteria);
    }

    protected function keywordMatch(string $criterionId, string $transcript, array $criterion): array
    {
        $passPhrases = $criterion['pass_phrases'] ?? [];
        $failPhrases = $criterion['fail_phrases'] ?? [];
        $isZtp = $criterion['ztp'] ?? false;

        $passCount = 0;
        $failCount = 0;
        $matchedPhrase = '';

        foreach ($passPhrases as $phrase) {
            if (strpos($transcript, strtolower($phrase)) !== false) {
                $passCount++;
                $matchedPhrase = $phrase;
            }
        }

        foreach ($failPhrases as $phrase) {
            if (strpos($transcript, strtolower($phrase)) !== false) {
                $failCount++;
                if (empty($matchedPhrase)) {
                    $matchedPhrase = $phrase;
                }
            }
        }

        if ($isZtp) {
            $pass = ($failCount === 0);
            $details = $pass ? 'No ZTP violations detected' : "ZTP violation: {$matchedPhrase}";
        } else {
            $pass = ($passCount > $failCount);
            $details = $pass ? "Matched: {$matchedPhrase}" : 'No clear pass phrases found';
        }

        return [
            'pass' => $pass,
            'details' => $details,
            'pass_count' => $passCount,
            'fail_count' => $failCount,
        ];
    }

    protected function calculateBreakdown(array $results): array
    {
        $breakdown = [
            'opening' => ['score' => 0, 'max' => 0],
            'probing' => ['score' => 0, 'max' => 0],
            'qualification' => ['score' => 0, 'max' => 0],
            'closing' => ['score' => 0, 'max' => 0],
            'compliance' => ['score' => 0, 'max' => 0],
        ];

        foreach ($results['rubric_results'] as $id => $result) {
            if ($result['na']) {
                continue;
            }

            $category = $result['category'];
            $points = $result['points'];

            if (isset($breakdown[$category])) {
                $breakdown[$category]['max'] += $points;
                if ($result['pass']) {
                    $breakdown[$category]['score'] += $points;
                }
            }
        }

        $results['rubric_breakdown'] = $breakdown;

        return $results;
    }

    protected function calculateScore(array $results): array
    {
        $hasZtpViolation = count($results['ztp_violations']) > 0;

        if ($hasZtpViolation) {
            $results['score'] = 0;
            $results['sentiment'] = 'negative';

            return $results;
        }

        $totalEarned = 0;
        $totalMax = 0;

        $breakdown = $results['rubric_breakdown'];
        $totalEarned = $breakdown['opening_score']
            + $breakdown['probing_score']
            + $breakdown['qualification_score']
            + $breakdown['closing_score']
            + $breakdown['compliance_score'];

        $totalMax = $breakdown['opening_max']
            + $breakdown['probing_max']
            + $breakdown['qualification_max']
            + $breakdown['closing_max']
            + $breakdown['compliance_max'];

        if ($totalMax > 0) {
            $results['score'] = round(($totalEarned / $totalMax) * 100);
        }

        if ($results['score'] >= 70) {
            $results['sentiment'] = 'positive';
        } elseif ($results['score'] >= 40) {
            $results['sentiment'] = 'neutral';
        } else {
            $results['sentiment'] = 'negative';
        }

        return $results;
    }

    protected function generateTags(array $results): array
    {
        $tags = [];
        $score = $results['score'];

        if ($score >= 85) {
            $tags[] = 'excellent';
        } elseif ($score >= 70) {
            $tags[] = 'good';
        } elseif ($score >= 50) {
            $tags[] = 'needs-improvement';
        } else {
            $tags[] = 'poor';
        }

        $failedCategories = [];
        foreach ($results['rubric_results'] as $id => $result) {
            if (! $result['pass'] && ! $result['na']) {
                $category = $result['category'];
                if (! in_array($category, $failedCategories)) {
                    $failedCategories[] = $category;
                    $tags[] = $category.'-gap';
                }

                if (in_array($id, ['3.4'])) {
                    $tags[] = 'unqualified-transfer';
                }
                if (in_array($id, ['5.1'])) {
                    $tags[] = 'hipaa-risk';
                }
                if (in_array($id, ['5.2'])) {
                    $tags[] = 'medical-advice-risk';
                }
            }
        }

        if (count($results['ztp_violations']) > 0) {
            $tags[] = 'ztp-violation';
        }

        $results['tags'] = $tags;

        return $results;
    }

    protected function generateSummary(array $results): array
    {
        $score = $results['score'];
        $passed = $results['passed_criteria'];
        $failed = $results['failed_criteria'];
        $ztpCount = count($results['ztp_violations']);

        $summary = "QA Score: {$score}/100 | {$passed}/{$results['total_criteria']} criteria passed | {$failed} failed";

        if ($ztpCount > 0) {
            $summary .= " | ZTP Violations: {$ztpCount} (".implode(', ', $results['ztp_violations']).')';
        }

        $results['summary'] = $summary;

        return $results;
    }

    protected function getDisposition(array $results): array
    {
        $score = $results['score'];
        $hasZtpViolation = count($results['ztp_violations']) > 0;
        $failed3_4 = in_array('3.4', array_column($results['rubric_results'], 'id'))
            && ! ($results['rubric_results']['3.4']['pass'] ?? true);

        if ($hasZtpViolation) {
            $results['disposition'] = 'auto-fail';

            return $results;
        }

        if ($failed3_4) {
            $results['disposition'] = 'unqualified';

            return $results;
        }

        if ($score >= 80) {
            $results['disposition'] = 'qualified';
        } elseif ($score >= 60) {
            $results['disposition'] = 'warm';
        } elseif ($score >= 40) {
            $results['disposition'] = 'refer';
        } else {
            $results['disposition'] = 'do-not-refer';
        }

        return $results;
    }

    public function detectTransfer(string $transcript): bool
    {
        if (empty(trim($transcript))) {
            return false;
        }

        $transferPatterns = [
            '/\btransfer\b/i',
            '/\btransferring\b/i',
            '/\blet me transfer\b/i',
            '/\btransfer you\b/i',
            '/\btransferring you\b/i',
            '/\bconnecting you to\b/i',
            '/\bone moment\b/i',
            '/\bhold on\b/i',
            '/\bwill transfer\b/i',
            '/\bpatch you through\b/i',
            '/\btransferring to\b/i',
            '/\btransferring call\b/i',
            '/\btransfer this\b/i',
            '/\bforwarding\b/i',
            '/\bforward to\b/i',
            '/\bswitching you\b/i',
            '/\blet me get\b/i',
            '/\bconnect you\b/i',
            '/\bconnecting\b/i',
        ];

        foreach ($transferPatterns as $pattern) {
            if (preg_match($pattern, $transcript)) {
                Log::debug('QAAnalysisService: Transfer detected', [
                    'pattern' => $pattern,
                    'matched' => true,
                ]);

                return true;
            }
        }

        return false;
    }

    public function generateCoachingInsights(array $analysis, string $transcript): ?array
    {
        $failedCriteria = [];
        $ztpViolations = [];

        foreach ($analysis['rubric_results'] ?? [] as $id => $result) {
            if (! ($result['na'] ?? false)) {
                if (! ($result['pass'] ?? false)) {
                    if ($result['ztp'] ?? false) {
                        $ztpViolations[$id] = $result;
                    } else {
                        $failedCriteria[$id] = $result;
                    }
                }
            }
        }

        if (empty($failedCriteria) && empty($ztpViolations)) {
            return [
                'coaching_insights' => 'Excellent performance — agent met all applicable quality standards. Continue reinforcing current best practices.',
                'recommendations' => 'No critical gaps identified. Focus on maintaining consistency and exploring advanced scenarios during coaching sessions.',
            ];
        }

        $breakdown = $analysis['rubric_breakdown'] ?? [];
        $score = $analysis['score'] ?? 0;
        $disposition = $analysis['disposition'] ?? 'unknown';

        $criteriaList = '';
        foreach (array_merge($ztpViolations, $failedCriteria) as $id => $result) {
            $severity = $result['severity'] ?? 'minor';
            $points = $result['points'] ?? 0;
            $criteriaList .= "- **Criterion {$id}:** {$result['criterion']} ({$severity}, {$points} pts) — {$result['details']}\n";
        }

        $systemPrompt = <<<'EOT'
You are a senior QA supervisor providing coaching feedback for a substance abuse helpline agent.
Write in professional supervisor voice — direct, constructive, actionable.
EOT;

        $userPrompt = <<<EOT
Call Quality Analysis — Coaching Report

Overall Score: {$score}/100
Disposition: {$disposition}

Category Breakdown:
EOT;

        foreach ($breakdown as $category => $data) {
            if (($data['max'] ?? 0) > 0) {
                $userPrompt .= '- '.ucfirst($category).": {$data['score']}/{$data['max']}\n";
            }
        }

        $userPrompt .= <<<EOT

Failed & ZTP Criteria:
{$criteriaList}

Return your response in exactly this format (two sections, nothing else):

COACHING_INSIGHTS:
[2-3 sentences of supervisor-level feedback addressing overall performance, key strengths, and the primary areas requiring attention. Be specific about which categories drove the score.]

RECOMMENDATIONS:
[Numbered list of 3-6 specific, actionable training recommendations. Each recommendation should reference the failed criterion ID and include a concrete coaching tip. ZTP violations must be addressed with highest priority.]
EOT;

        $result = $this->openRouter->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ]);

        if (! $result) {
            Log::warning('QAAnalysisService: Failed to generate coaching insights via AI');

            return null;
        }

        $content = $result['choices'][0]['message']['content'] ?? '';

        $coachingInsights = null;
        $recommendations = null;

        if (preg_match('/COACHING_INSIGHTS:\s*\n(.*?)(?=RECOMMENDATIONS:|$)/si', $content, $insightsMatch)) {
            $coachingInsights = trim($insightsMatch[1]);
        }

        if (preg_match('/RECOMMENDATIONS:\s*\n(.*?)$/si', $content, $recMatch)) {
            $recommendations = trim($recMatch[1]);
        }

        if (! $coachingInsights && ! $recommendations) {
            Log::warning('QAAnalysisService: Could not parse coaching insights from AI response', [
                'content' => substr($content, 0, 500),
            ]);

            return null;
        }

        return [
            'coaching_insights' => $coachingInsights,
            'recommendations' => $recommendations,
        ];
    }
}
