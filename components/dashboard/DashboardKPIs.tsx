import React from 'react'
import { cn } from '@/lib/utils'

export interface KPIStats {
  // Volume
  totalCalls: number
  answered: number
  missed: number
  voicemail: number
  // Handle Time (in seconds)
  avgDuration: number
  avgTalkTime: number
  avgWaitTime: number
  avgRingTime: number
  // QA Performance
  avgScore: number
  scoreDistribution: {
    excellent: number  // 85-100
    good: number       // 70-84
    needsImprovement: number  // 50-69
    poor: number       // <50
  }
  // ZTP Violations
  ztpViolations: {
    hipaaRisk: number
    medicalAdviceRisk: number
    unqualifiedTransfer: number
  }
  // Disposition
  disposition: {
    qualified: number   // Score 80-100
    warmLead: number     // Score 60-79
    refer: number        // Score 40-59
    doNotRefer: number   // Score <40 or 0
  }
}

function formatSeconds(seconds: number): string {
  if (!seconds || isNaN(seconds)) return '0:00'
  const mins = Math.floor(seconds / 60)
  const secs = Math.round(seconds % 60)
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

function StatCard({ label, value, subValue, icon, className }: {
  label: string
  value: string | number
  subValue?: string
  icon: React.ReactNode
  className?: string
}) {
  return (
    <div className={cn('bg-navy-50 rounded-xl p-4 border border-navy-100', className)}>
      <div className="flex items-start justify-between">
        <div>
          <p className="text-xs font-medium text-navy-500 uppercase tracking-wide">{label}</p>
          <p className="text-2xl font-bold text-navy-900 mt-1">{value}</p>
          {subValue && <p className="text-xs text-navy-400 mt-0.5">{subValue}</p>}
        </div>
        <div className="w-10 h-10 rounded-lg bg-navy-100 flex items-center justify-center text-navy-600">
          {icon}
        </div>
      </div>
    </div>
  )
}

function MiniBar({ value, max, color }: { value: number; max: number; color: string }) {
  const pct = max > 0 ? Math.min((value / max) * 100, 100) : 0
  return (
    <div className="w-full bg-navy-100 rounded-full h-2">
      <div className={cn('h-2 rounded-full transition-all', color)} style={{ width: `${pct}%` }} />
    </div>
  )
}

interface DashboardKPIsProps {
  stats: KPIStats
}

export default function DashboardKPIs({ stats }: DashboardKPIsProps) {
  const answerRate = stats.totalCalls > 0 ? ((stats.answered / stats.totalCalls) * 100).toFixed(1) : '0'
  const missedRate = stats.totalCalls > 0 ? ((stats.missed / stats.totalCalls) * 100).toFixed(1) : '0'
  const voicemailRate = stats.totalCalls > 0 ? ((stats.voicemail / stats.totalCalls) * 100).toFixed(1) : '0'

  const scoreTotal = stats.scoreDistribution.excellent + stats.scoreDistribution.good +
                     stats.scoreDistribution.needsImprovement + stats.scoreDistribution.poor

  return (
    <div className="space-y-6">
      {/* Volume Metrics */}
      <div>
        <h3 className="text-sm font-semibold text-navy-700 uppercase tracking-wide mb-3">Volume</h3>
        <div className="grid grid-cols-2 lg:grid-cols-5 gap-3">
          <StatCard
            label="Total Calls"
            value={stats.totalCalls}
            icon={<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>}
          />
          <StatCard
            label="Answered"
            value={stats.answered}
            subValue={`${answerRate}%`}
            icon={<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg>}
          />
          <StatCard
            label="Missed"
            value={stats.missed}
            subValue={`${missedRate}%`}
            icon={<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" /></svg>}
          />
          <StatCard
            label="Voicemail"
            value={stats.voicemail}
            subValue={`${voicemailRate}%`}
            icon={<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>}
          />
          <StatCard
            label="Answer Rate"
            value={`${answerRate}%`}
            subValue="Target: 90%"
            icon={<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>}
          />
        </div>
      </div>

      {/* Handle Time */}
      <div>
        <h3 className="text-sm font-semibold text-navy-700 uppercase tracking-wide mb-3">Handle Time</h3>
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
          <StatCard
            label="Avg Duration"
            value={formatSeconds(stats.avgDuration)}
            subValue="Total call time"
            icon={<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>}
          />
          <StatCard
            label="Avg Talk Time"
            value={formatSeconds(stats.avgTalkTime)}
            subValue="Agent conversation"
            icon={<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>}
          />
          <StatCard
            label="Avg Wait Time"
            value={formatSeconds(stats.avgWaitTime)}
            subValue="Queue time"
            icon={<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>}
          />
          <StatCard
            label="Avg Ring Time"
            value={formatSeconds(stats.avgRingTime)}
            subValue="Time to answer"
            icon={<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>}
          />
        </div>
      </div>

      {/* QA Performance */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Score Overview */}
        <div className="bg-navy-50 rounded-xl p-4 border border-navy-100">
          <div className="flex items-start justify-between mb-4">
            <div>
              <p className="text-xs font-medium text-navy-500 uppercase tracking-wide">AI Score</p>
              <p className="text-3xl font-bold text-navy-900">{stats.avgScore.toFixed(1)}%</p>
            </div>
            <div className="text-right">
              <p className="text-xs text-navy-400">Total Analyzed</p>
              <p className="text-sm font-semibold text-navy-700">{scoreTotal}</p>
            </div>
          </div>

          <div className="space-y-2">
            <div className="flex items-center justify-between">
              <span className="text-xs text-navy-600">Excellent (85-100)</span>
              <span className="text-xs font-semibold text-navy-700">{stats.scoreDistribution.excellent}</span>
            </div>
            <MiniBar value={stats.scoreDistribution.excellent} max={scoreTotal || 1} color="bg-green-500" />

            <div className="flex items-center justify-between">
              <span className="text-xs text-navy-600">Good (70-84)</span>
              <span className="text-xs font-semibold text-navy-700">{stats.scoreDistribution.good}</span>
            </div>
            <MiniBar value={stats.scoreDistribution.good} max={scoreTotal || 1} color="bg-emerald-500" />

            <div className="flex items-center justify-between">
              <span className="text-xs text-navy-600">Needs Improvement (50-69)</span>
              <span className="text-xs font-semibold text-navy-700">{stats.scoreDistribution.needsImprovement}</span>
            </div>
            <MiniBar value={stats.scoreDistribution.needsImprovement} max={scoreTotal || 1} color="bg-amber-500" />

            <div className="flex items-center justify-between">
              <span className="text-xs text-navy-600">Poor (&lt;50)</span>
              <span className="text-xs font-semibold text-navy-700">{stats.scoreDistribution.poor}</span>
            </div>
            <MiniBar value={stats.scoreDistribution.poor} max={scoreTotal || 1} color="bg-red-500" />
          </div>
        </div>

        {/* ZTP Violations */}
        <div className="bg-navy-50 rounded-xl p-4 border border-navy-100">
          <p className="text-xs font-medium text-navy-500 uppercase tracking-wide mb-4">ZTP Violations</p>
          <div className="space-y-4">
            <div className="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-100">
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                  <svg className="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                  <p className="text-sm font-medium text-red-700">HIPAA Risk</p>
                  <p className="text-xs text-red-500">Confidentiality violation</p>
                </div>
              </div>
              <span className="text-xl font-bold text-red-600">{stats.ztpViolations.hipaaRisk}</span>
            </div>

            <div className="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-100">
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                  <svg className="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                </div>
                <div>
                  <p className="text-sm font-medium text-red-700">Medical Advice</p>
                  <p className="text-xs text-red-500">Providing medical guidance</p>
                </div>
              </div>
              <span className="text-xl font-bold text-red-600">{stats.ztpViolations.medicalAdviceRisk}</span>
            </div>

            <div className="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-100">
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                  <svg className="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                </div>
                <div>
                  <p className="text-sm font-medium text-red-700">Unqualified Transfer</p>
                  <p className="text-xs text-red-500">State insurance / self-pay</p>
                </div>
              </div>
              <span className="text-xl font-bold text-red-600">{stats.ztpViolations.unqualifiedTransfer}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Disposition */}
      <div className="bg-navy-50 rounded-xl p-4 border border-navy-100">
        <p className="text-xs font-medium text-navy-500 uppercase tracking-wide mb-4">Call Disposition</p>
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <div className="text-center p-3 bg-green-50 rounded-lg border border-green-100">
            <p className="text-2xl font-bold text-green-700">{stats.disposition.qualified}</p>
            <p className="text-xs font-medium text-green-600 mt-1">Qualified Lead</p>
            <p className="text-xs text-green-500">Score 80-100</p>
          </div>
          <div className="text-center p-3 bg-emerald-50 rounded-lg border border-emerald-100">
            <p className="text-2xl font-bold text-emerald-700">{stats.disposition.warmLead}</p>
            <p className="text-xs font-medium text-emerald-600 mt-1">Warm Lead</p>
            <p className="text-xs text-emerald-500">Score 60-79</p>
          </div>
          <div className="text-center p-3 bg-amber-50 rounded-lg border border-amber-100">
            <p className="text-2xl font-bold text-amber-700">{stats.disposition.refer}</p>
            <p className="text-xs font-medium text-amber-600 mt-1">Refer</p>
            <p className="text-xs text-amber-500">Score 40-59</p>
          </div>
          <div className="text-center p-3 bg-red-50 rounded-lg border border-red-100">
            <p className="text-2xl font-bold text-red-700">{stats.disposition.doNotRefer}</p>
            <p className="text-xs font-medium text-red-600 mt-1">Do Not Refer</p>
            <p className="text-xs text-red-500">Score &lt;40</p>
          </div>
        </div>
      </div>
    </div>
  )
}