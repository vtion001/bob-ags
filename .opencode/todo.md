# Mission: Fix Score Breakdown + Add AI Coaching Insights

## G1: Fix Score Breakdown | status: completed

### T1.1: Refactor calculateBreakdown() in QAAnalysisService | agent:Worker
- [x] T1.1.1: Change flat structure to nested by category | file:app/Services/QAAnalysisService.php
- [x] T1.1.2: VERIFY view condition passes (is_array check) — PHP syntax clean

## G2: Add Coaching Insights + Recommendations | status: completed

### T2.1: Database Migration | agent:Worker
- [x] T2.1.1: CREATE migration `2026_04_17_000001_add_coaching_fields_to_qa_logs_table.php`
- [x] T2.1.2: RUN migration — both coaching_insights + recommendations columns added

### T2.2: QaLog Model Update | agent:Worker
- [x] T2.2.1: ADD fillable for coaching_insights + recommendations

### T2.3: AI Coaching Method | agent:Worker
- [x] T2.3.1: ADD generateCoachingInsights() to QAAnalysisService — supervisor voice, structured output

### T2.4: Wire Into Controller | agent:Worker
- [x] T2.4.1: CALL generateCoachingInsights() after analyzeTranscript() in analyze()
- [x] T2.4.2: STORE coaching_insights + recommendations in QaLog::updateOrCreate()

### T2.5: UI Sections | agent:Worker
- [x] T2.5.1: ADD Coaching Insights section (blue-50) below rubric table
- [x] T2.5.2: ADD Recommended Training Focus section (amber-50) below rubric table

## G3: Verification | status: completed

### T3.1: Test Suite | agent:Reviewer
- [x] T3.1.1: `php artisan test` — 24 pre-existing failures (sessions table), 1 pass, 0 new failures
- [x] T3.1.2: PHP syntax check — app/Services/QAAnalysisService.php, app/Http/Controllers/CallController.php, app/Models/QaLog.php — all clean
- [x] T3.1.3: Migration applied — php artisan migrate --force ✓
