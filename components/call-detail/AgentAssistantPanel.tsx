'use client'

import React, { useState, useEffect } from 'react'
import Card from '@/components/ui/Card'

interface AgentSuggestion {
  id: string
  type: 'script' | 'reminder' | 'transfer' | 'warning'
  priority: 'high' | 'medium' | 'low'
  title: string
  message: string
  criterion?: string
}

interface AgentAssistantPanelProps {
  missingCriteria: string[]
  currentContext: {
    insurance?: string
    state?: string
    substance?: string
    callerName?: string
    isCrisis?: boolean
  }
  lastTranscript?: string
}

const SUGGESTIONS: Record<string, { title: string; message: string; type: AgentSuggestion['type']; priority: AgentSuggestion['priority'] }> = {
  '1.1': {
    title: 'Use Approved Greeting',
    message: 'Say: "Hello Flyland, this is [Your Name]. Thank you for calling. How can I help you today?"',
    type: 'script',
    priority: 'high'
  },
  '1.2': {
    title: 'Confirm Caller Name',
    message: 'Ask: "Can I get your name and your relationship to the person struggling with substance use?"',
    type: 'script',
    priority: 'medium'
  },
  '1.3': {
    title: 'Identify Reason for Call',
    message: 'Ask: "What brings you to our helpline today?" - Do not assume the reason.',
    type: 'script',
    priority: 'high'
  },
  '1.4': {
    title: 'Verify Location',
    message: 'Ask: "What state are you located in?" and repeat it back to confirm.',
    type: 'script',
    priority: 'high'
  },
  '2.1': {
    title: 'Ask About Sobriety',
    message: 'Ask: "When was your last drink or drug use?" - Wait for a specific timeframe. Do NOT ask "how long sober."',
    type: 'script',
    priority: 'high'
  },
  '2.2': {
    title: 'Identify Substance',
    message: 'Ask: "What substance or substances are you struggling with?"',
    type: 'script',
    priority: 'high'
  },
  '2.3': {
    title: 'Verify Insurance',
    message: 'Ask: "Can you tell me what type of insurance you have? Is it private, Medicaid, Medicare, or self-pay?"',
    type: 'script',
    priority: 'high'
  },
  '2.5': {
    title: 'Confirm Phone Number',
    message: 'Confirm the best phone number to reach them for follow-up.',
    type: 'reminder',
    priority: 'medium'
  },
  '3.4': {
    title: 'CRITICAL: No Unqualified Transfers',
    message: 'Do NOT transfer Medicaid/Medicare callers to treatment centers. Use SAMHSA helpline first.',
    type: 'warning',
    priority: 'high'
  },
  '3.5': {
    title: 'Prepare for Transfer',
    message: 'When ready: "I am going to transfer you now to our admissions team. Please hold for just a moment."',
    type: 'transfer',
    priority: 'high'
  },
  '3.6': {
    title: 'Provide Resources',
    message: 'For non-qualifying callers: "Here is the 988 Lifeline number you can call anytime: 988."',
    type: 'transfer',
    priority: 'medium'
  },
  '3.7': {
    title: 'Show Empathy',
    message: 'Use phrases: "I understand this is difficult" or "Thank you for sharing with me."',
    type: 'script',
    priority: 'medium'
  },
  '5.1': {
    title: 'HIPAA WARNING',
    message: 'Do NOT repeat caller information loudly. Document securely. Never leave voicemails with treatment details.',
    type: 'warning',
    priority: 'high'
  },
  '5.2': {
    title: 'No Medical Advice',
    message: 'Do NOT give medical advice. Say: "I am not a medical professional, but I can connect you with resources."',
    type: 'warning',
    priority: 'high'
  }
}

export default function AgentAssistantPanel({ missingCriteria, currentContext, lastTranscript }: AgentAssistantPanelProps) {
  const [suggestions, setSuggestions] = useState<AgentSuggestion[]>([])
  const [expanded, setExpanded] = useState(true)

  useEffect(() => {
    const newSuggestions: AgentSuggestion[] = []
    
    for (const criterion of missingCriteria.slice(0, 6)) {
      const suggestion = SUGGESTIONS[criterion]
      if (suggestion) {
        newSuggestions.push({
          id: criterion,
          ...suggestion,
          criterion
        })
      }
    }

    // Add crisis warning if applicable
    if (currentContext.isCrisis) {
      newSuggestions.unshift({
        id: 'crisis',
        type: 'warning',
        priority: 'high',
        title: 'Crisis Detected',
        message: 'Transfer to 988 Suicide & Crisis Lifeline. Available 24/7: Call or text 988.'
      })
    }

    // Add transfer reminder if all criteria met
    if (missingCriteria.length === 0 && currentContext.insurance && currentContext.state) {
      newSuggestions.push({
        id: 'ready',
        type: 'transfer',
        priority: 'medium',
        title: 'Ready to Transfer',
        message: 'All qualification criteria met. Prepare caller for transfer to admissions.'
      })
    }

    setSuggestions(newSuggestions)
  }, [missingCriteria, currentContext, lastTranscript])

  const getTypeIcon = (type: AgentSuggestion['type']) => {
    switch (type) {
      case 'script':
        return (
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
        )
      case 'warning':
        return (
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        )
      case 'transfer':
        return (
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 5l7 7-7 7M5 5l7 7-7 7" />
          </svg>
        )
      case 'reminder':
        return (
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        )
    }
  }

  const getPriorityColor = (priority: AgentSuggestion['priority']) => {
    switch (priority) {
      case 'high':
        return 'text-red-600 bg-red-50 border-red-200'
      case 'medium':
        return 'text-amber-600 bg-amber-50 border-amber-200'
      case 'low':
        return 'text-blue-600 bg-blue-50 border-blue-200'
    }
  }

  const getTypeColor = (type: AgentSuggestion['type']) => {
    switch (type) {
      case 'script':
        return 'text-navy-600'
      case 'warning':
        return 'text-red-600'
      case 'transfer':
        return 'text-green-600'
      case 'reminder':
        return 'text-blue-600'
    }
  }

  return (
    <Card className="p-0 overflow-hidden">
      <button
        onClick={() => setExpanded(!expanded)}
        className="w-full p-4 flex items-center justify-between hover:bg-navy-50 transition-colors"
      >
        <div className="flex items-center gap-2">
          <svg className="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
          </svg>
          <h3 className="text-lg font-bold text-navy-900">AI Agent Assistant</h3>
          {suggestions.length > 0 && (
            <span className={`px-2 py-0.5 rounded-full text-xs font-semibold ${suggestions.some(s => s.priority === 'high') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'}`}>
              {suggestions.length} suggestion{suggestions.length > 1 ? 's' : ''}
            </span>
          )}
        </div>
        <svg
          className={`w-5 h-5 text-navy-400 transition-transform ${expanded ? 'rotate-180' : ''}`}
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      {expanded && (
        <div className="max-h-80 overflow-y-auto divide-y divide-navy-100">
          {suggestions.length === 0 ? (
            <div className="p-4 text-center">
              <p className="text-green-600 font-medium">All criteria met!</p>
              <p className="text-navy-500 text-sm mt-1">Call is proceeding well.</p>
            </div>
          ) : (
            suggestions.map((suggestion) => (
              <div
                key={suggestion.id}
                className={`p-4 border-l-4 ${suggestion.priority === 'high' ? 'border-l-red-500' : suggestion.priority === 'medium' ? 'border-l-amber-500' : 'border-l-blue-500'}`}
              >
                <div className="flex items-start gap-3">
                  <div className={`mt-0.5 ${getTypeColor(suggestion.type)}`}>
                    {getTypeIcon(suggestion.type)}
                  </div>
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2 mb-1">
                      <p className="text-sm font-semibold text-navy-900">{suggestion.title}</p>
                      {suggestion.criterion && (
                        <span className="text-[10px] px-1.5 py-0.5 bg-navy-100 text-navy-600 rounded font-mono">
                          {suggestion.criterion}
                        </span>
                      )}
                    </div>
                    <p className="text-sm text-navy-600">{suggestion.message}</p>
                  </div>
                </div>
              </div>
            ))
          )}
        </div>
      )}
    </Card>
  )
}
