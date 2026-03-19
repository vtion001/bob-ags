'use client'

import React from 'react'
import { useParams, useRouter } from 'next/navigation'
import Button from '@/components/ui/Button'
import Card from '@/components/ui/Card'
import ScoreCircle from '@/components/ScoreCircle'
import { mockCalls } from '@/lib/mockData'

export default function CallDetailPage() {
  const params = useParams()
  const router = useRouter()
  const callId = params.id as string

  const call = mockCalls.find(c => c.id === callId)

  if (!call) {
    return (
      <div className="p-6 lg:p-8">
        <Button variant="ghost" onClick={() => router.back()} className="mb-6">
          ← Back
        </Button>
        <Card className="text-center py-12">
          <p className="text-slate-400">Call not found</p>
        </Card>
      </div>
    )
  }

  const formatDuration = (seconds: number) => {
    const mins = Math.floor(seconds / 60)
    const secs = seconds % 60
    return `${mins}m ${secs}s`
  }

  return (
    <div className="p-6 lg:p-8 max-w-6xl mx-auto">
      {/* Header */}
      <Button variant="ghost" onClick={() => router.back()} className="mb-6">
        ← Back
      </Button>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left column - Score and basic info */}
        <div className="lg:col-span-1">
          <Card className="flex flex-col items-center p-8 text-center">
            <ScoreCircle score={call.score || 0} size="md" />
          </Card>

          <Card className="mt-6 p-6">
            <h3 className="text-sm font-semibold text-slate-400 mb-4">Caller Info</h3>
            <div className="space-y-4">
              <div>
                <p className="text-xs text-slate-500 uppercase">Phone</p>
                <p className="text-white font-mono mt-1">{call.phone}</p>
              </div>
              <div>
                <p className="text-xs text-slate-500 uppercase">Duration</p>
                <p className="text-white mt-1">{formatDuration(call.duration)}</p>
              </div>
              <div>
                <p className="text-xs text-slate-500 uppercase">Direction</p>
                <p className="text-white capitalize mt-1">{call.direction}</p>
              </div>
              <div>
                <p className="text-xs text-slate-500 uppercase">Status</p>
                <p className="text-cyan-400 capitalize mt-1">{call.status}</p>
              </div>
            </div>
          </Card>

          <div className="flex gap-2 mt-6">
            <Button variant="secondary" size="sm" className="flex-1">
              Export
            </Button>
            <Button variant="secondary" size="sm" className="flex-1">
              Notes
            </Button>
          </div>
        </div>

        {/* Right column - Analysis and transcript */}
        <div className="lg:col-span-2 space-y-6">
          {/* Analysis */}
          {call.analysis && (
            <Card className="p-6">
              <h3 className="text-lg font-bold text-white mb-4">AI Analysis</h3>
              
              <div className="space-y-4">
                <div>
                  <p className="text-sm text-slate-400 mb-2">Sentiment</p>
                  <span className={`inline-block px-3 py-1 rounded-full text-sm font-semibold ${
                    call.analysis.sentiment === 'positive' ? 'bg-green-500/20 text-green-400' :
                    call.analysis.sentiment === 'negative' ? 'bg-red-500/20 text-red-400' :
                    'bg-slate-500/20 text-slate-400'
                  }`}>
                    {call.analysis.sentiment}
                  </span>
                </div>

                <div>
                  <p className="text-sm text-slate-400 mb-2">Summary</p>
                  <p className="text-white">{call.analysis.summary}</p>
                </div>

                <div>
                  <p className="text-sm text-slate-400 mb-2">Tags</p>
                  <div className="flex flex-wrap gap-2">
                    {call.analysis.tags.map(tag => (
                      <span key={tag} className="px-3 py-1 bg-cyan-500/20 text-cyan-300 text-xs rounded-full">
                        {tag}
                      </span>
                    ))}
                  </div>
                </div>

                <div>
                  <p className="text-sm text-slate-400 mb-2">Suggested Disposition</p>
                  <p className="text-white bg-navy-900/50 rounded-lg p-3">{call.analysis.disposition}</p>
                </div>
              </div>
            </Card>
          )}

          {/* Transcript */}
          <Card className="p-6">
            <h3 className="text-lg font-bold text-white mb-4">Transcript</h3>
            <div className="bg-navy-900/50 rounded-lg p-4 text-slate-300 text-sm leading-relaxed space-y-3">
              <div className="flex gap-3">
                <span className="font-semibold text-cyan-400 flex-shrink-0">Agent:</span>
                <span>Good afternoon, thank you for calling. How can I help you today?</span>
              </div>
              <div className="flex gap-3">
                <span className="font-semibold text-emerald-400 flex-shrink-0">Caller:</span>
                <span>Hi, I'm interested in learning more about your premium plan.</span>
              </div>
              <div className="flex gap-3">
                <span className="font-semibold text-cyan-400 flex-shrink-0">Agent:</span>
                <span>Great! I'd be happy to help. Let me go over the key features with you...</span>
              </div>
            </div>
          </Card>

          {/* Actions */}
          <div className="flex flex-wrap gap-2">
            <Button variant="primary" size="md">
              Create Task
            </Button>
            <Button variant="secondary" size="md">
              Add to Salesforce
            </Button>
            <Button variant="ghost" size="md">
              Schedule Follow-up
            </Button>
          </div>
        </div>
      </div>
    </div>
  )
}
