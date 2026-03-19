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
}

// Mock AI analysis - in production, this would call OpenRouter API
export async function analyzeTranscript(transcript: string, phone: string, client?: string): Promise<Analysis> {
  // Simulate API call
  await new Promise(resolve => setTimeout(resolve, 1000))

  // Generate score based on keywords in transcript
  const positiveKeywords = ['interested', 'premium', 'budget', 'approved', 'decision-maker', 'timeline']
  const negativeKeywords = ['budget', 'constraints', 'not interested', 'later', 'consider']
  
  let score = 50
  const lowerTranscript = transcript.toLowerCase()
  
  positiveKeywords.forEach(keyword => {
    if (lowerTranscript.includes(keyword)) score += 8
  })
  
  negativeKeywords.forEach(keyword => {
    if (lowerTranscript.includes(keyword)) score -= 5
  })
  
  score = Math.min(100, Math.max(0, score))

  // Determine sentiment
  let sentiment: 'positive' | 'neutral' | 'negative' = 'neutral'
  if (score >= 70) sentiment = 'positive'
  else if (score <= 30) sentiment = 'negative'

  // Generate tags based on analysis
  const tags: string[] = []
  if (score >= 75) tags.push('hot-lead')
  else if (score >= 50) tags.push('warm-lead')
  else tags.push('cold-lead')

  if (lowerTranscript.includes('decision-maker')) tags.push('decision-maker')
  if (lowerTranscript.includes('budget')) tags.push('budget-aware')
  if (lowerTranscript.includes('timeline')) tags.push('timeline-set')

  return {
    qualification_score: Math.round(score),
    sentiment,
    summary: generateSummary(transcript, score),
    tags,
    suggested_disposition: getSuggestedDisposition(score),
    follow_up_required: score >= 40,
    call_type: detectCallType(transcript),
    mentioned_names: extractNames(transcript),
    mentioned_locations: extractLocations(transcript),
    salesforce_notes: generateSalesforceNotes(transcript, score),
  }
}

function generateSummary(transcript: string, score: number): string {
  if (score >= 75) {
    return 'Strong fit with clear purchasing intent. Caller demonstrated decision-making authority and has approved budget.'
  } else if (score >= 50) {
    return 'Good potential lead. Caller showed interest but needs additional information before proceeding.'
  } else {
    return 'Low qualification score. Caller requires nurturing or may not be a good fit for current offerings.'
  }
}

function getSuggestedDisposition(score: number): string {
  if (score >= 75) {
    return 'Send contract and follow up within 24 hours'
  } else if (score >= 50) {
    return 'Send proposal and schedule follow-up call in 3-5 days'
  } else if (score >= 25) {
    return 'Add to nurture sequence, follow up in 2 weeks'
  } else {
    return 'Archive or add to long-term nurture list'
  }
}

function detectCallType(transcript: string): string {
  const lowerTranscript = transcript.toLowerCase()
  if (lowerTranscript.includes('renewal') || lowerTranscript.includes('existing')) return 'renewal'
  if (lowerTranscript.includes('support') || lowerTranscript.includes('issue')) return 'support'
  if (lowerTranscript.includes('demo') || lowerTranscript.includes('trial')) return 'demo'
  return 'sales'
}

function extractNames(transcript: string): string[] {
  // Simple extraction - in production, use NLP library
  const names: string[] = []
  const namePattern = /(?:my name is|I'm|this is)\s+([A-Z][a-z]+)/gi
  let match
  while ((match = namePattern.exec(transcript)) !== null) {
    names.push(match[1])
  }
  return [...new Set(names)]
}

function extractLocations(transcript: string): string[] {
  // Simple extraction - in production, use NLP library
  const locations: string[] = []
  const locationKeywords = ['california', 'texas', 'florida', 'new york', 'chicago', 'seattle', 'denver']
  const lowerTranscript = transcript.toLowerCase()
  
  locationKeywords.forEach(location => {
    if (lowerTranscript.includes(location)) {
      locations.push(location.charAt(0).toUpperCase() + location.slice(1))
    }
  })
  
  return locations
}

function generateSalesforceNotes(transcript: string, score: number): string {
  const sentiment = score >= 70 ? 'positive' : score >= 40 ? 'neutral' : 'negative'
  return `Call analysis: ${sentiment} sentiment. Score: ${score}/100. Contact appears to be a ${score >= 75 ? 'strong' : score >= 50 ? 'qualified' : 'warm'} prospect.`
}
