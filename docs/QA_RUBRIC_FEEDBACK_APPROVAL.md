# QA Rubric Feedback — Approval Request

**Date:** March 24, 2026  
**Status:** Pending Approval  
**Prepared by:** BOB System  
**Reviewers:** QA Manager, Compliance Officer

---

## Purpose

This document outlines the discrepancies between the **Feedback Dump** (human-annotated QA criteria from call reviews) and the **AI Scoring System** (current 25-criterion automated rubric in bob-ags).

Approval is requested to align the AI scoring system with the validated human feedback before full deployment.

---

## Summary of Changes

| Category | Current AI System | Proposed Change | Priority |
|----------|-------------------|-----------------|----------|
| ZTP Criteria | 3 criteria present (3.4, 5.1, 5.2) | **Keep as-is** | Critical |
| Always-N/A Items | None auto-marked | **Mark 4.2, 4.3, 4.4 as N/A** (manual Salesforce check) | High |
| 1.2 Scope | Treatment calls only | **Add "facility inquiry"** as trigger | Medium |
| Missing Criteria | 12 criteria absent | **Do not add** (Feedback Dump is simplified) | Low |
| CTM Star Rating | Not integrated | **Add CTM star rating to 4.3** | Medium |

---

## Detailed Discrepancies

### 1. Always-N/A Criteria (4.2, 4.3, 4.4)

**Current State (AI System):**
- 4.2, 4.3, 4.4 are evaluated from transcript content
- No auto-N/A logic

**Feedback Dump States:**
> *"4.2, 4.3, 4.4 need to be checked in Salesforce manually — cannot be captured from call alone, so always N/A in Analyzer"*

> *"For 4.3 — CTM records the star rating, include this"*

**Proposed Change:**
- Mark criteria 4.2, 4.3, 4.4 as **always N/A** in transcript-based analysis
- For 4.3 specifically: pull star rating from **CTM recording metadata** (not transcript)
- Rationale: These require manual Salesforce verification post-call

**Impact:**
- Score will exclude these criteria from denominator calculation
- Must update `lib/ai.ts` to mark these as N/A and adjust score formula

---

### 2. Criterion 1.2 — Scope Expansion

**Current State (AI System):**
> *"Applicable when the caller is seeking treatment; mark N/A if meeting call"*

**Feedback Dump States:**
> *"Applicable when the caller is seeking treatment, and facility inquiry; mark N/A if meeting call"*

**Proposed Change:**
- Update 1.2 to also trigger for **facility inquiry** calls
- N/A only when call is strictly a **meeting call**

**Impact:**
- Minor scope expansion; no point value change

---

### 3. Missing Criteria from Feedback Dump

The Feedback Dump contains **13 criteria** vs. the AI System's **25 criteria**.

| Missing from Feedback | AI System Status | Recommendation |
|---------------------|------------------|----------------|
| 1.3 (reason for call) | Present | Keep in AI system |
| 2.4 (additional info) | Present | Keep in AI system |
| 3.3 (approved rebuttals) | Present | Keep in AI system |
| 3.4 (unqualified transfers) | Present, ZTP | Keep — critical compliance |
| 3.5 (escalate qualified) | Present | Keep in AI system |
| 3.6 (correct referrals) | Present | Keep in AI system |
| 3.7 (empathy) | Present | Keep in AI system |
| 4.1 (professional close) | Present | Keep in AI system |
| 4.4 (callback requests) | Present | Keep in AI system |
| 5.1 (HIPAA) | Present, ZTP | Keep — critical compliance |
| 5.2 (medical advice) | Present, ZTP | Keep — critical compliance |
| 5.3 (response time) | Present | Keep in AI system |
| 5.4 (soft skills) | Present | Keep in AI system |

**Recommendation:**  
The Feedback Dump appears to be a **simplified driver-specific rubric**. The AI system's 25 criteria are more comprehensive and should be retained. The discrepancies above represent additions/changes to the AI system, not removals.

---

## ZTP Criteria — No Changes Required

The Feedback Dump does **not include** any ZTP (Zero Tolerance Policy) criteria. However, the AI system correctly includes:

| Criterion | Description | Status |
|-----------|-------------|--------|
| 3.4 | Avoided unqualified transfers | **Keep — Auto-FAIL** |
| 5.1 | HIPAA Confidentiality | **Keep — Auto-FAIL** |
| 5.2 | No Medical Advice | **Keep — Auto-FAIL** |

**Note:** These were likely omitted from the Feedback Dump because they represent severe violations that require supervisor review rather than standard QA scoring. They should remain in the AI system as they catch critical compliance issues.

---

## Approval Checklist

- [ ] **QA Manager approves** — Always-N/A logic for 4.2, 4.3, 4.4
- [ ] **QA Manager approves** — CTM star rating integration for 4.3
- [ ] **QA Manager approves** — 1.2 scope expansion to include facility inquiries
- [ ] **Compliance Officer confirms** — ZTP criteria (3.4, 5.1, 5.2) remain active
- [ ] **Director approves** — Full 25-criteria rubric retained (vs. simplified 13-criteria)

---

## Files to Modify

If approved, the following files require changes:

| File | Change |
|------|--------|
| `lib/ai.ts` | Add N/A logic for 4.2, 4.3, 4.4; update 1.2 scope; add CTM star rating fetch |
| `docs/AI_SCORING_SYSTEM.md` | Document always-N/A criteria |
| `docs/QA_ANALYSIS_KNOWLEDGE_BASE.md` | Update criterion 1.2 description and 4.2/4.3/4.4 notes |

---

## Next Steps

1. Review this document with QA Manager and Compliance Officer
2. Obtain signatures/approvals on the checklist above
3. If approved, implement changes in `lib/ai.ts`
4. Run test calls to validate N/A behavior
5. Update documentation files
6. Deploy to production

---

## Contact

For questions about this document, contact the BOB development team.
