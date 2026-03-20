export interface CriterionResult {
  id: string
  criterion: string
  pass: boolean
  ztp: boolean
  autoFail: boolean
  details: string
  deduction: number
  severity: string
  category: string
}

export interface RubricBreakdown {
  opening_score: number
  opening_max: number
  probing_score: number
  probing_max: number
  qualification_score_detail: number
  qualification_max: number
  closing_score: number
  closing_max: number
  compliance_score: number
  compliance_max: number
}

export interface Analysis {
  qualification_score: number
  sentiment: 'positive' | 'neutral' | 'negative'
  summary: string
  tags: string[]
  suggested_disposition: string
  follow_up_required: boolean
  call_type: string
  detected_state?: string
  detected_insurance?: string
  mentioned_names: string[]
  mentioned_locations: string[]
  salesforce_notes: string
  rubric_results?: CriterionResult[]
  rubric_breakdown?: RubricBreakdown
}

export const RUBRIC_CRITERIA = [
  { id: '1.1', name: 'Used approved greeting', category: 'Opening', severity: 'Minor', deduction: 2, passPhrases: ['hello flyland', 'flyland this is'], failPhrases: ['hi there', 'flyland help line'], ztp: false, autoFail: false },
  { id: '1.2', name: 'Confirmed caller name and relationship', category: 'Opening', severity: 'Minor', deduction: 2, passPhrases: ["what's your name", 'can i get your name', 'may i have your name'], failPhrases: [], ztp: false, autoFail: false },
  { id: '1.3', name: 'Identified reason for call promptly', category: 'Opening', severity: 'Major', deduction: 5, passPhrases: ['how can i help', 'what brings you', 'reason for your call'], failPhrases: ['assumed reason', 'jumped to questions'], ztp: false, autoFail: false },
  { id: '1.4', name: 'Verified caller location (state)', category: 'Opening', severity: 'Major', deduction: 5, passPhrases: ['what state', 'which state', 'located in', 'state are you'], failPhrases: ['never asks state'], ztp: false, autoFail: false },
  { id: '2.1', name: 'Asked about sober/clean time', category: 'Probing', severity: 'Major', deduction: 5, passPhrases: ['last drink', 'last drug use', 'when was your last', 'how long has it been'], failPhrases: ['how long sober', 'skips time'], ztp: false, autoFail: false },
  { id: '2.2', name: 'Inquired about substance/type of struggle', category: 'Probing', severity: 'Major', deduction: 5, passPhrases: ['what substance', 'struggling with', 'alcohol drugs', 'drug or alcohol'], failPhrases: ['gives detox advice'], ztp: false, autoFail: false },
  { id: '2.3', name: 'Asked about insurance type and details', category: 'Probing', severity: 'Major', deduction: 5, passPhrases: ['type of insurance', 'private or state', 'medicaid', 'medicare', 'insurance do you have'], failPhrases: ['only asks do you have insurance', 'skips insurance'], ztp: false, autoFail: false },
  { id: '2.4', name: 'Gathered additional info concisely', category: 'Probing', severity: 'Minor', deduction: 2, passPhrases: ['openness to help', 'facility name', 'follow-up'], failPhrases: ['probes too many times', 'repeated questions'], ztp: false, autoFail: false },
  { id: '2.5', name: 'Verified caller phone number', category: 'Probing', severity: 'Minor', deduction: 2, passPhrases: ['best number', 'phone number', 'reach you'], failPhrases: ['skips phone when needed'], ztp: false, autoFail: false },
  { id: '3.1', name: 'Correctly assessed eligibility', category: 'Qualification', severity: 'Major', deduction: 5, passPhrases: ['transferring you', 'referring to', 'qualified'], failPhrases: ['wrong transfer', 'wrong referral', 'offers self-pay when prohibited'], ztp: false, autoFail: false },
  { id: '3.2', name: 'Handled caller-specific needs correctly', category: 'Qualification', severity: 'Major', deduction: 5, passPhrases: ['treatment', 'samhsa', 'al-anon', 'aa', 'na'], failPhrases: ['wrong resource', 'incorrect referral'], ztp: false, autoFail: false },
  { id: '3.3', name: 'Used approved rebuttals/scripts', category: 'Qualification', severity: 'Major', deduction: 5, passPhrases: ['we are a helpline', 'to best help you', 'approved rebuttal'], failPhrases: ['deviates script', 'pressures caller'], ztp: false, autoFail: false },
  { id: '3.4', name: 'Avoided unqualified transfers', category: 'Qualification', severity: 'ZTP', deduction: 0, passPhrases: ['does not transfer state insurance', 'no transfer for self-pay', 'correctly disqualified'], failPhrases: ['transfers state insurance', 'transfers self-pay', 'unqualified transfer'], ztp: true, autoFail: true },
  { id: '3.5', name: 'Escalated qualified leads promptly', category: 'Qualification', severity: 'Major', deduction: 5, passPhrases: ['transferring now', 'let me get you', 'transfer in'], failPhrases: ['delays transfer', 'fails to tag'], ztp: false, autoFail: false },
  { id: '3.6', name: 'Provided correct referrals for non-qualifying', category: 'Qualification', severity: 'Major', deduction: 5, passPhrases: ['988', 'samhsa', 'here are resources'], failPhrases: ['wrong referral', 'missing referral'], ztp: false, autoFail: false },
  { id: '3.7', name: 'Maintained empathy and professionalism', category: 'Qualification', severity: 'Minor', deduction: 2, passPhrases: ['i understand', 'thank you for', "that's understandable", 'appreciate you'], failPhrases: ['irritation', 'no empathy', 'dismissive'], ztp: false, autoFail: false },
  { id: '4.1', name: 'Ended call professionally', category: 'Closing', severity: 'Minor', deduction: 2, passPhrases: ['let me get you', 'here are the resources', 'thank you for calling', 'transferring now'], failPhrases: ['abrupt hang-up', 'unclear next steps'], ztp: false, autoFail: false },
  { id: '4.2', name: 'Documented in Salesforce within 5 minutes', category: 'Closing', severity: 'Major', deduction: 5, passPhrases: ['documented', 'logged', 'salesforce', 'notes taken'], failPhrases: ['no documentation', 'late documentation'], ztp: false, autoFail: false },
  { id: '4.3', name: 'Applied correct star rating/disposition', category: 'Closing', severity: 'Major', deduction: 5, passPhrases: ['4 stars', 'qualified transfer', 'correct rating'], failPhrases: ['wrong stars', 'incorrect disposition'], ztp: false, autoFail: false },
  { id: '4.4', name: 'Noted follow-up/callback requests', category: 'Closing', severity: 'Minor', deduction: 2, passPhrases: ['callback request', 'follow-up noted', 'will call back'], failPhrases: ['callback omitted'], ztp: false, autoFail: false },
  { id: '5.1', name: 'Upheld patient confidentiality (HIPAA)', category: 'Compliance', severity: 'ZTP', deduction: 0, passPhrases: ['hipaa', 'confidential', 'protected health'], failPhrases: ['shares info unauthorized', 'hipaa breach', 'unauthorized disclosure'], ztp: true, autoFail: true },
  { id: '5.2', name: 'Avoided providing medical advice', category: 'Compliance', severity: 'ZTP', deduction: 0, passPhrases: ['i cannot advise', 'not a medical', 'consult a professional'], failPhrases: ['detox advice', 'withdrawal advice', 'dosage', 'treatment recommendation'], ztp: true, autoFail: true },
  { id: '5.3', name: 'Maintained response time', category: 'Compliance', severity: 'Minor', deduction: 2, passPhrases: ['responding promptly', 'answered quickly'], failPhrases: ['delayed response'], ztp: false, autoFail: false },
  { id: '5.4', name: 'Demonstrated soft skills', category: 'Compliance', severity: 'Minor', deduction: 2, passPhrases: ['active listening', 'clear communication', 'professional'], failPhrases: ['interruptions', 'unclear'], ztp: false, autoFail: false },
  { id: '5.5', name: 'Adhered to SOP/tools', category: 'Compliance', severity: 'Major', deduction: 5, passPhrases: ['using ctm', 'using zoho', 'approved tools'], failPhrases: ['unapproved script', 'deviates from tools'], ztp: false, autoFail: false },
]

export async function analyzeTranscript(transcript: string, phone: string, client?: string): Promise<Analysis> {
  const apiKey = process.env.OPENROUTER_API_KEY
  const lower = transcript.toLowerCase()

  let aiResults: Record<string, { pass: boolean; details: string }> = {}

  if (apiKey) {
    try {
      const response = await fetch('https://openrouter.ai/api/v1/chat/completions', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${apiKey}`,
          'Content-Type': 'application/json',
          'HTTP-Referer': process.env.NEXT_PUBLIC_SITE_URL || 'http://localhost:3002',
          'X-Title': 'BOB Call Analysis',
        },
        body: JSON.stringify({
          model: 'anthropic/claude-3-haiku',
          messages: [{ role: 'user', content: buildRubricPrompt(transcript) }],
          max_tokens: 2000,
          temperature: 0.1,
        }),
      })

      if (response.ok) {
        const data = await response.json()
        const content = data.choices?.[0]?.message?.content || ''
        aiResults = parseRubricResults(content)
      }
    } catch (err) {
      console.error('OpenRouter analysis error:', err)
    }
  }

  const results = evaluateRubric(lower, aiResults)
  const { breakdown, ztpFailures, autoFailed } = calculateBreakdown(results)
  let score = calculateScore(breakdown, ztpFailures, autoFailed)

  const mentionedNames = extractNames(transcript)
  const mentionedLocations = extractLocations(transcript)
  const detectedState = mentionedLocations[0] || ''
  const detectedInsurance = detectInsurance(transcript)
  const callType = detectCallType(transcript)
  const tags = generateTags(results, score, detectedInsurance, detectedState)
  const sentiment = score >= 70 ? 'positive' : score >= 40 ? 'neutral' : 'negative'
  const disposition = getDisposition(results, score, autoFailed)

  return {
    qualification_score: score,
    sentiment,
    summary: generateSummary(results, score, autoFailed),
    tags,
    suggested_disposition: disposition,
    follow_up_required: results.some(r => r.id === '2.5' && !r.pass) || score >= 60,
    call_type: callType,
    detected_state: detectedState,
    detected_insurance: detectedInsurance,
    mentioned_names: mentionedNames,
    mentioned_locations: mentionedLocations,
    salesforce_notes: generateSalesforceNotes(results, score, autoFailed, mentionedNames),
    rubric_results: results,
    rubric_breakdown: breakdown,
  }
}

function buildRubricPrompt(transcript: string): string {
  return `You are a quality assurance analyst for a substance abuse helpline. Analyze the following call transcript and evaluate it against each criterion.

For EACH of the 25 criteria below, respond with PASS or FAIL and a brief reason.

Return your response in this exact format for each criterion (one per line):
CRITERION_ID|PASS/FAIL|Brief reason

Criteria:
1.1 Opening - Used approved greeting: Agent says "Hello Flyland, this is [Agent Name]"
1.2 Opening - Confirmed caller name and relationship (if Al-Anon/family)
1.3 Opening - Identified reason for call promptly (first 30 seconds) without assumptions
1.4 Opening - Verified caller location (state) - agent asks and repeats back state
2.1 Probing - Asked about sober/clean time using "When was your last drink or drug use?"
2.2 Probing - Inquired about substance/type of struggle (no advice given)
2.3 Probing - Asked about insurance type: "private through work/family or state like Medicaid/Medicare?"
2.4 Probing - Gathered additional relevant info concisely (3 turns or fewer)
2.5 Probing - Verified caller phone number for follow-up
3.1 Qualification - Correctly assessed eligibility and action
3.2 Qualification - Handled caller-specific needs correctly (treatment/Al-Anon/facility/other)
3.3 Qualification - Used approved rebuttals/scripts for refusals
3.4 Qualification - Avoided unqualified transfers (ZTP - Auto-FAIL if violated)
3.5 Qualification - Escalated qualified leads promptly (within 60 seconds)
3.6 Qualification - Provided correct referrals for non-qualifying cases
3.7 Qualification - Maintained empathy (uses name 2x+, empathetic statements)
4.1 Closing - Ended call professionally with clear next steps
4.2 Closing - Documented in Salesforce within 5 minutes
4.3 Closing - Applied correct star rating (4 stars = qualified, 2 = unqualified)
4.4 Closing - Noted follow-up/callback requests
5.1 Compliance - Upheld patient confidentiality (HIPAA) (ZTP - Auto-FAIL if violated)
5.2 Compliance - Avoided providing medical advice (ZTP - Auto-FAIL if violated)
5.3 Compliance - Maintained response time (under 30 seconds)
5.4 Compliance - Demonstrated soft skills (active listening, clear communication)
5.5 Compliance - Adhered to SOP/tools (used CTM/ZohoChat only)

TRANSCRIPT:
---
${transcript}
---

Return exactly 25 lines, one for each criterion in order.`
}

function parseRubricResults(content: string): Record<string, { pass: boolean; details: string }> {
  const results: Record<string, { pass: boolean; details: string }> = {}
  const lines = content.split('\n').filter(l => l.trim())
  for (const line of lines) {
    const parts = line.split('|')
    if (parts.length >= 3) {
      const id = parts[0].trim()
      const status = parts[1].trim().toUpperCase()
      const details = parts.slice(2).join('|').trim()
      if (/^\d+\.\d+$/.test(id)) {
        results[id] = { pass: status === 'PASS', details: details.substring(0, 200) }
      }
    }
  }
  return results
}

function evaluateRubric(lower: string, aiResults: Record<string, { pass: boolean; details: string }>): CriterionResult[] {
  return RUBRIC_CRITERIA.map(criterion => {
    const aiResult = aiResults[criterion.id]
    const pass = aiResult ? aiResult.pass : keywordMatch(lower, criterion)
    const details = aiResult?.details || generateDetails(lower, criterion)
    return {
      id: criterion.id,
      criterion: criterion.name,
      pass,
      ztp: criterion.ztp,
      autoFail: criterion.autoFail,
      details,
      deduction: pass ? 0 : criterion.deduction,
      severity: criterion.severity,
      category: criterion.category,
    }
  })
}

function keywordMatch(lower: string, criterion: typeof RUBRIC_CRITERIA[0]): boolean {
  const passCount = criterion.passPhrases.filter(p => lower.includes(p.toLowerCase())).length
  const failCount = criterion.failPhrases.filter(p => lower.includes(p.toLowerCase())).length
  if (criterion.ztp || criterion.autoFail) return failCount === 0
  return passCount > failCount
}

function generateDetails(lower: string, criterion: typeof RUBRIC_CRITERIA[0]): string {
  const found = criterion.passPhrases.find(p => lower.includes(p.toLowerCase()))
  if (found) return `Detected: "${found}"`
  const missed = criterion.failPhrases.find(p => lower.includes(p.toLowerCase()))
  if (missed) return `Issue detected: "${missed}"`
  return criterion.ztp || criterion.autoFail ? 'No violations detected' : 'Not clearly detected in transcript'
}

function calculateBreakdown(results: CriterionResult[]) {
  const breakdown = { opening_score: 0, opening_max: 0, probing_score: 0, probing_max: 0, qualification_score_detail: 0, qualification_max: 0, closing_score: 0, closing_max: 0, compliance_score: 0, compliance_max: 0 }
  let ztpFailures = 0
  let autoFailed = false

  for (const r of results) {
    const points = r.ztp ? 10 : r.severity === 'Minor' ? 2 : r.severity === 'Major' ? 5 : 0
    const key = r.category === 'Opening' ? 'opening' : r.category === 'Probing' ? 'probing' : r.category === 'Qualification' ? 'qualification' : r.category === 'Closing' ? 'closing' : 'compliance'
    const scoreKey = `${key}_score` as keyof typeof breakdown
    const maxKey = `${key}_max` as keyof typeof breakdown
    breakdown[maxKey] += points
    if (r.pass) breakdown[scoreKey] += points
    if (r.ztp && !r.pass) ztpFailures++
    if (r.autoFail && !r.pass) autoFailed = true
  }

  return { breakdown, ztpFailures, autoFailed }
}

function calculateScore(breakdown: ReturnType<typeof calculateBreakdown>['breakdown'], ztpFailures: number, autoFailed: boolean): number {
  if (autoFailed || ztpFailures >= 2) return 0
  const totalMax = breakdown.opening_max + breakdown.probing_max + breakdown.qualification_max + breakdown.closing_max + breakdown.compliance_max
  const totalScore = breakdown.opening_score + breakdown.probing_score + breakdown.qualification_score_detail + breakdown.closing_score + breakdown.compliance_score
  if (totalMax === 0) return 50
  return Math.round((totalScore / totalMax) * 100)
}

function generateTags(results: CriterionResult[], score: number, insurance: string, state: string): string[] {
  const tags: string[] = []
  if (score >= 85) tags.push('excellent')
  else if (score >= 70) tags.push('good')
  else if (score >= 50) tags.push('needs-improvement')
  else tags.push('poor')
  const failed = results.filter(r => !r.pass)
  const categories = [...new Set(failed.map(r => r.category))]
  for (const cat of categories) tags.push(`${cat.toLowerCase()}-gap`)
  if (results.find(r => r.id === '3.4' && !r.pass)) tags.push('unqualified-transfer')
  if (results.find(r => r.id === '5.1' && !r.pass)) tags.push('hipaa-risk')
  if (results.find(r => r.id === '5.2' && !r.pass)) tags.push('medical-advice-risk')
  const ztpFails = results.filter(r => !r.pass && r.ztp)
  if (ztpFails.length > 0) tags.push('ztp-violation')
  if (insurance) tags.push(`insurance:${insurance}`)
  if (state) tags.push(`state:${state}`)
  return [...new Set(tags)]
}

function generateSummary(results: CriterionResult[], score: number, autoFailed: boolean): string {
  if (autoFailed) return 'Auto-failed due to critical compliance violation (ZTP). Call requires immediate supervisor review.'
  const failed = results.filter(r => !r.pass)
  const categories = [...new Set(failed.map(r => r.category))]
  if (categories.length === 0) return 'Excellent call. Agent followed all quality standards across all categories.'
  const worst = failed.filter(r => r.severity === 'Major' || r.severity === 'ZTP')
  const categorySummary = categories.map(c => {
    const catFails = failed.filter(r => r.category === c)
    return `${c} (${catFails.length} issue${catFails.length > 1 ? 's' : ''})`
  }).join(', ')
  if (worst.length > 0) return `Call scored ${score}/100. Major issues in: ${categorySummary}. Requires coaching on critical criteria.`
  return `Call scored ${score}/100. Minor issues in: ${categorySummary}. Generally good performance with room for refinement.`
}

function getDisposition(results: CriterionResult[], score: number, autoFailed: boolean): string {
  if (autoFailed) return 'Auto-fail: Critical violation - Requires supervisor review'
  const qualify3 = results.find(r => r.id === '3.4')
  if (qualify3 && !qualify3.pass) return 'Unqualified - Do not transfer (state insurance/self-pay/out-of-state/VA/Kaiser)'
  if (score >= 80) return 'Qualified Lead - Transfer to treatment facility (tag: Qualified Transfer, 4 stars)'
  if (score >= 60) return 'Warm Lead - Provide resources and schedule callback (3 stars)'
  if (score >= 40) return 'Refer - Provide SAMHSA/988 and general resources (2 stars)'
  return 'Do Not Refer - Outside scope or not interested (1 star)'
}

function generateSalesforceNotes(results: CriterionResult[], score: number, autoFailed: boolean, names: string[]): string {
  const passed = results.filter(r => r.pass)
  const failed = results.filter(r => !r.pass)
  const ztpFailed = failed.filter(r => r.ztp)
  let notes = `QA Score: ${score}/100 | ${passed.length}/25 criteria passed | ${failed.length} failed`
  if (autoFailed) notes += ' | STATUS: AUTO-FAIL - Critical violation detected'
  if (ztpFailed.length > 0) notes += ` | ZTP Violations: ${ztpFailed.length}`
  if (names.length > 0) notes += ` | Caller: ${names[0]}`
  const majorFails = failed.filter(r => r.severity === 'Major' || r.severity === 'ZTP')
  if (majorFails.length > 0) notes += ` | Critical: ${majorFails.map(r => r.id).join(', ')}`
  return notes
}

function extractNames(transcript: string): string[] {
  const names: string[] = []
  const namePattern = /(?:my name is|I'm|this is|name's)\s+([A-Z][a-z]+)/gi
  let match
  while ((match = namePattern.exec(transcript)) !== null) names.push(match[1])
  return [...new Set(names)]
}

function extractLocations(transcript: string): string[] {
  const locations: string[] = []
  const usStates = ['alabama','alaska','arizona','arkansas','california','colorado','connecticut','delaware','florida','georgia','hawaii','idaho','illinois','indiana','iowa','kansas','kentucky','louisiana','maine','maryland','massachusetts','michigan','minnesota','mississippi','missouri','montana','nebraska','nevada','new hampshire','new jersey','new mexico','new york','north carolina','north dakota','ohio','oklahoma','oregon','pennsylvania','rhode island','south carolina','south dakota','tennessee','texas','utah','vermont','virginia','washington','west virginia','wisconsin','wyoming']
  const lower = transcript.toLowerCase()
  for (const state of usStates) {
    if (lower.includes(state)) locations.push(state.charAt(0).toUpperCase() + state.slice(1))
  }
  return [...new Set(locations)]
}

function detectInsurance(transcript: string): string {
  const lower = transcript.toLowerCase()
  if (lower.includes('medicaid')) return 'medicaid'
  if (lower.includes('medicare')) return 'medicare'
  if (lower.includes('tricare')) return 'tricare'
  if (lower.includes('kaiser')) return 'kaiser'
  if (lower.includes('private') || lower.includes('blue cross') || lower.includes('aetna') || lower.includes('cigna') || lower.includes('united')) return 'private'
  if (lower.includes('self pay') || lower.includes('self-pay')) return 'self-pay'
  return ''
}

function detectCallType(transcript: string): string {
  const lower = transcript.toLowerCase()
  if (lower.includes('al-anon') || lower.includes('family')) return 'al-anon'
  if (lower.includes('facility') || lower.includes('treatment center')) return 'facility'
  if (lower.includes('looking for help') || lower.includes('addiction')) return 'treatment'
  return 'general'
}
