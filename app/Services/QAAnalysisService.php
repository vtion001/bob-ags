<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QAAnalysisService
{
    protected OpenRouterService $openRouter;
    protected array $rubric;
    protected array $criteria;
    protected array $ztpCriteria;
    protected array $alwaysNaCriteria;

    public function __construct(OpenRouterService $openRouter)
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

            if ($criterion['ztp'] && !$pass) {
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
                'autoFail' => $criterion['ztp'] && !$pass,
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

    protected function analyzeWithAI(string $transcriptText): array
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
            $details = $pass ? "Matched: {$matchedPhrase}" : "No clear pass phrases found";
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
            'opening_score' => 0,
            'opening_max' => 0,
            'probing_score' => 0,
            'probing_max' => 0,
            'qualification_score' => 0,
            'qualification_max' => 0,
            'closing_score' => 0,
            'closing_max' => 0,
            'compliance_score' => 0,
            'compliance_max' => 0,
        ];

        foreach ($results['rubric_results'] as $id => $result) {
            if ($result['na']) continue;

            $category = $result['category'];
            $points = $result['points'];

            switch ($category) {
                case 'opening':
                    $breakdown['opening_max'] += $points;
                    if ($result['pass']) {
                        $breakdown['opening_score'] += $points;
                    }
                    break;
                case 'probing':
                    $breakdown['probing_max'] += $points;
                    if ($result['pass']) {
                        $breakdown['probing_score'] += $points;
                    }
                    break;
                case 'qualification':
                    $breakdown['qualification_max'] += $points;
                    if ($result['pass']) {
                        $breakdown['qualification_score'] += $points;
                    }
                    break;
                case 'closing':
                    $breakdown['closing_max'] += $points;
                    if ($result['pass']) {
                        $breakdown['closing_score'] += $points;
                    }
                    break;
                case 'compliance':
                    $breakdown['compliance_max'] += $points;
                    if ($result['pass']) {
                        $breakdown['compliance_score'] += $points;
                    }
                    break;
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
            if (!$result['pass'] && !$result['na']) {
                $category = $result['category'];
                if (!in_array($category, $failedCategories)) {
                    $failedCategories[] = $category;
                    $tags[] = $category . '-gap';
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
            $summary .= " | ZTP Violations: {$ztpCount} (" . implode(', ', $results['ztp_violations']) . ")";
        }

        $results['summary'] = $summary;
        return $results;
    }

    protected function getDisposition(array $results): array
    {
        $score = $results['score'];
        $hasZtpViolation = count($results['ztp_violations']) > 0;
        $failed3_4 = in_array('3.4', array_column($results['rubric_results'], 'id'))
            && !($results['rubric_results']['3.4']['pass'] ?? true);

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
}
